<?php

namespace Bayne\Behat;

use Bayne\Behat\Context\Initializer\AssertionContextInitializer;
use Bayne\Behat\Context\Initializer\ProfilerContextInitializer;
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
                ->scalarNode('build_path')->defaultValue('%paths.base%/build/behat')->end()
                ->scalarNode('screenshot_path')->defaultValue('%build_path%/screenshots')->end()
                ->scalarNode('json_filename')->defaultValue('report.json')->end()
                ->scalarNode('json_output_path')->defaultValue('%build_path%')->end()
                ->scalarNode('json_profiler_path')->defaultValue('%build_path%/profiler')->end()
                ->scalarNode('manual_filename')->defaultValue('output.md')->end()
                ->scalarNode('manual_path')->defaultValue('%build_path%/manual')->end()
                ->scalarNode('manual_tagname')->defaultValue('manually')->end()
                ->scalarNode('manual_screenshot_path')->defaultValue('%build_path%/manual/screenshots')->end()
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
        $definition->addArgument($config['manual_filename']);
        $definition->addArgument($config['manual_path']);
        $definition->addArgument($config['manual_tagname']);
        $definition->addArgument($config['manual_screenshot_path']);

        $container
            ->setDefinition('bayne.manual_screenshot.formatter', $definition)
            ->addTag('output.formatter')
        ;

        $definition = new Definition(ScreenshotContextInitializer::class);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $definition->addArgument($config['manual_screenshot_path']);
        $definition->addArgument($config['screenshot_path']);
        $definition->addArgument($config['manual_tagname']);
        $container->setDefinition('bayne.screenshot.context_initializer', $definition);

        $definition = new Definition(JsonFormatter::class);

        $definition->addArgument($config['json_filename']);
        $definition->addArgument($config['json_output_path']);
        $definition->addArgument($config['json_profiler_path']);
        $definition->addArgument($config['manual_screenshot_path']);

        $container
            ->setDefinition('bayne.json.formatter', $definition)
            ->addTag('output.formatter')
        ;

        $definition = new Definition(AssertionContextInitializer::class);
        $definition->addArgument($config['build_path']);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('bayne.assertion.context_initializer', $definition);

        $definition = new Definition(ProfilerContextInitializer::class);
        $definition->addArgument($config['manual_tagname']);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, array('priority' => 0));
        $container->setDefinition('bayne.profiler.context_initializer', $definition);

        $container->setParameter('build_path', $config['build_path']);
    }
}
