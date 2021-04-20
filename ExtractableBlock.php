<?php

namespace Motto;

use Motto\BlockExtractor;

class ExtractableBlock {

    protected $extractor;
    protected $name = false;
    protected $classes;
    protected $config;
    protected $block;

    public function __construct( $block, BlockExtractor $extractor )
    {
        $this->block = $block;
        $this->extractor = $extractor;
        $this->classes = $this->block['attrs']['className'] ? explode(' ', $this->block['attrs']['className']) : [];
        $this->match( $this->block );
    }

    public function __toString()
    {
        return $this->render();
    }

    public function get()
    {
        return $this->block;
    }

    public function render()
    {
        return \render_block( $this->block );
    }

    public function extractable()
    {
        return (bool) $this->name;
    }

    public function getActionName()
    {
        return $this->config['action'];
    }

    public function getClassFilter()
    {
        return $this->config['class'];
    }

    public function getContainerClasses( $extra = null )
    {
        if( $extra )
            $this->classes[] = $extra;

        return implode(' ', $this->classes);
    }

    public function removeContainerClasses()
    {
        $this->block['innerHTML'] = str_replace(
            ' ' . $this->block['attrs']['className'] . '"', '"', $this->block['innerHTML']
        );
        foreach( $this->block['innerContent'] as $i => $string ) {
            if( is_string($string) )
                $this->block['innerContent'][$i] = str_replace(
                    ' ' . $this->block['attrs']['className'] . '"', '"', $string
                );    
        }

        $this->block['attrs']['className'] = '';
    }

    protected function match()
    {
        $matchingClasses = array_values(
            array_intersect($this->extractor->keys(), $this->classes)
        );

        if( !empty($matchingClasses) ) {
            $this->name = $matchingClasses[0];
            $this->config = $this->extractor->config($this->name);
        }
    }

}