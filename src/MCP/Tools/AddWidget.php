<?php

declare(strict_types=1);

namespace App\MCP\Tools;

use Mcp\Capability\Attribute\McpTool;

#[McpTool(
    name: 'add-widget',
    description: 'Add a new widget. Provide class name and title.'
)]
final readonly class AddWidget
{
    public function __invoke(
        string $className,
        string $title
    ): string {
        $namespace = "App\\Extension\\Default\\Widgets";
        $filePath = "src/Extension/Default/Widgets/{$className}.php";

        $template = <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('eventsourcerer.widget')]
final readonly class {{className}}
{
    public function getTitle(): string
    {
        return '{{title}}';
    }
}
PHP;

        $template = str_replace(
            ['{{namespace}}', '{{className}}', '{{title}}'],
            [$namespace, $className, $title],
            $template
        );

        return "Save the following file to: {$filePath}\n\n{$template}";
    }
}
