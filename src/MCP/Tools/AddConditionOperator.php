<?php

declare(strict_types=1);

namespace App\MCP\Tools;

use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'add-condition-operator',
    description: 'Add a new condition operator. Provide class name, label, and PHP logic for the calculation (receives $value and $parameter).'
)]
final readonly class AddConditionOperator
{
    public function __invoke(
        string $className,
        string $label,
        string $phpLogic
    ): string {
        $namespace = "App\\Extension\\Default\\ConditionOperators";
        $filePath = "src/Extension/Default/ConditionOperators/{$className}.php";

        $template = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use App\Domain\Projection\Model\ConditionLabel;
use App\Domain\Projection\Model\SystemConditionOperator;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.condition_operator')]
final readonly class {{className}} extends SystemConditionOperator
{
    public function compute(mixed $value, mixed $parameter): bool
    {
        {{phpLogic}}
    }

    public static function label(): ConditionLabel
    {
        return ConditionLabel::fromString('{{label}}');
    }
}
PHP;

        $template = str_replace(
            ['{{namespace}}', '{{className}}', '{{phpLogic}}', '{{label}}'],
            [$namespace, $className, $phpLogic, $label],
            $template
        );

        return "Save the following file to: {$filePath}\n\n{$template}";
    }
}
