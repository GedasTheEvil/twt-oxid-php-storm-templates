<?php

$env = $argv[1] ?? 'vagrant';

class StringToClassNameTransformer
{
    const TRANSFORMED_SUFFIX = 'fullModules.';

    private $confDir;
    private $aModules = [];

    /**
     * Transformer constructor.
     *
     * @param string $env
     */
    public function __construct(string $env)
    {
        $this->confDir = dirname(__FILE__) . '/dev/config/' . $env . '/modules/';
    }

    /**
     * Save.
     *
     * @param string $fileName
     *
     * @return void
     * @author Gediminas Skucas <gediminas.skucas@twt.de>
     */
    public function save(string $fileName = '1.php')
    {
        include $this->confDir . $fileName;
        $converted = $this->confDir . self::TRANSFORMED_SUFFIX . $fileName;
        file_put_contents($converted, $this->wrapInArray($this->getTransformed()));
    }

    /**
     * Returns transformed.
     *
     * @return array
     * @author Gediminas Skucas <gediminas.skucas@twt.de>
     */
    private function getTransformed()
    {
        return array_map(function ($a) {
            $chain = explode('&', $a);

            if (!is_array($chain)) {
                $chain = [$a];
            }

            return $chain;
        }, $this->aModules);
    }

    /**
     * Wrap in array.
     *
     * @param array $modules
     *
     * @return string
     * @author Gediminas Skucas <gediminas.skucas@twt.de>
     */
    private function wrapInArray(array $modules)
    {
        $result = "<?php\n";
        $result .= "/*\n * Module Chain\n */\n\n";
        $result .= "\$this->aModules = [\n";

        foreach ($modules as $mainModule => $extends) {
            $result .= "    {$this->getModuleCass($mainModule)} => [\n";

            foreach ($extends as $childModule) {
                $class = $this->getModuleCass($childModule);

                if ($class !== '') {
                    $result .= "        {$class},\n";
                }
            }

            $result .= "    ],\n";
        }

        return $result . "];\n";
    }

    /**
     * Returns module cass.
     *
     * @param $mainModule
     *
     * @return string
     * @author Gediminas Skucas <gediminas.skucas@twt.de>
     */
    private function getModuleCass($mainModule)
    {
        $mainModule = trim((string)$mainModule);

        if ($mainModule === '') {
            return '';
        }

        if (strpos($mainModule, '/') !== false) {
            return "'{$mainModule}'";
        }

        return "{$mainModule}::class";
    }
}

(new StringToClassNameTransformer($env))->save('1.php');
