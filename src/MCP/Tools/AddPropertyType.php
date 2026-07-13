<?php

declare(strict_types=1);

namespace App\MCP\Tools;

use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'add-property-type',
    description: 'Add a new property type to the application. Provide class name, description, package name, and optional PHP logic for serialization/deserialization/validation.'
)]
final readonly class AddPropertyType
{
    public function __invoke(
        string $className,
        string $description,
        string $packageName,
        string $serializeLogic = 'return $value;',
        string $deserializeLogic = 'return $value;',
        string $validateLogic = ''
    ): string {
        $namespace = "App\\Extension\\Packages\\{$packageName}\\PropertyType";
        $filePath = "src/Extension/Packages/{$packageName}/PropertyType/{$className}.php";

        $template = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use App\Domain\Common\Model\Author;
use App\Domain\Common\Model\Package;
use App\Domain\Common\Model\PropertyType;
use App\Domain\Common\Model\PropertyTypeDescription;
use App\Extension\Default\ConditionOperators\EqualTo;
use App\Extension\Default\ConditionOperators\NotEqualTo;
use App\Extension\Default\PropertyType\PropertyTypeComparison;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.property_type')]
final readonly class {{className}} implements PropertyType
{
    use PropertyTypeComparison;

    public static function author(): Author
    {
        return Author::eventSourcerer();
    }

    public static function create(): PropertyType
    {
        return new self();
    }

    public static function name(): PropertyTypeDescription
    {
        return PropertyTypeDescription::fromString('{{description}}');
    }

    public static function serialize(mixed $value): string
    {
        {{serializeLogic}}
    }

    public static function deserialize(string $value): mixed
    {
        {{deserializeLogic}}
    }

    public static function validate(mixed $value): void
    {
        {{validateLogic}}
    }

    public static function conditionOperators(): array
    {
        return [
            EqualTo::class,
            NotEqualTo::class,
        ];
    }

    public static function package(): Package
    {
        return Package::fromString('{{packageName}}');
    }

    public static function toString(string $value): string
    {
        return $value;
    }

    public static function exampleInput(): string
    {
        return '';
    }
}
PHP;

        $template = str_replace(
            ['{{namespace}}', '{{className}}', '{{description}}', '{{serializeLogic}}', '{{deserializeLogic}}', '{{validateLogic}}', '{{packageName}}'],
            [$namespace, $className, $description, $serializeLogic, $deserializeLogic, $validateLogic, $packageName],
            $template
        );

        return "Save the following file to: {$filePath}\n\n{$template}";
    }
}
