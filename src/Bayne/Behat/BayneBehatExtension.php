<?php

namespace Bayne\Behat;

use Bayne\Behat\Context\Initializer\ScreenshotContextInitializer;
use Bayne\Behat\Output\Formatter\JsonFormatter;
use Bayne\Behat\Output\Formatter\ManualScreenshotFormatter;
use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BayneBehatExtension implements Extension
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'bayne';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('screenshot_path')->end()
                ->arrayNode('json')
                    ->children()
                        ->scalarNode('filename')->defaultValue('report.json')->end()
                        ->scalarNode('output_path')->defaultValue('build/tests')->end()
                        ->scalarNode('profiler_path')->defaultValue('build/behat/profiler')->end()
                ->end()
            ->end()
                ->arrayNode('manual')
                    ->children()
                        ->scalarNode("filename")->isRequired()->end()
                        ->scalarNode("path")->isRequired()->end()
                        ->scalarNode("tagname")->isRequired()->end()
                        ->scalarNode("screenshot_path")->isRequired()->end()
                ->end()
            ->end()
       ;
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $definition = new Definition(ManualScreenshotFormatter::class);
        $definition->addArgument($config['manual']['filename']);
        $definition->addArgument($config['manual']['path']);
        $definition->addArgument($config['manual']['tagname']);
        $definition->addArgument($config['manual']['screenshot_path']);

        $container
            ->setDefinition("bayne.manual_screenshot.formatter", $definition)
            ->addTag("output.formatter")
        ;

        $definition = new Definition(ScreenshotContextInitializer::class);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $definition->addArgument($config['manual']['screenshot_path']);
        $definition->addArgument($config['screenshot_path']);
        $container->setDefinition('bayne.screenshot.context_initializer', $definition);

        $definition = new Definition(JsonFormatter::class);

        $definition->addArgument($config['json']['filename']);
        $definition->addArgument($config['json']['output_path']);
        $definition->addArgument($config['json']['profiler_path']);

        $container
            ->setDefinition('bayne.json.formatter', $definition)
            ->addTag('output.formatter')
        ;
    }
}
