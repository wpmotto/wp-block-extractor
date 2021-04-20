<?php

namespace Motto;

class BlockExtractor {

    protected $config = [
        // 'motto-fold' => [
        //     'class' => 'motto/fold-class',
        //     'action' => 'motto/fold',
        // ],
        'motto-graphics' => [
            'class' => 'motto/graphics-class',
            'action' => 'motto/graphics',
        ],
    ];
    protected $filteredContent;

    public function filterBlocks()
    {
        foreach ( $this->getBlocks() as $block ) {
            $block = new ExtractableBlock($block, $this);

            if ( $block->extractable() )
                $this->load( $block );
            else
                $this->filteredContent .= $block->render();
        }      

        // Remove wpautop filter so we do not get paragraphs for two line breaks in the content.
        $priority = has_filter( 'the_content', 'wpautop' );
        if ( false !== $priority ) {
            remove_filter( 'the_content', 'wpautop', $priority );
        }

        add_filter( 'the_content', [$this, 'filterContent'], 1, 10 );    
    }
    
    public function load( ExtractableBlock $block )
    {
        $block->removeContainerClasses();

        add_filter($block->getClassFilter(), function($class) use ($block) {
            return $block->getContainerClasses( $class );
        });

        add_action($block->getActionName(), function() use ($block) {
            echo $block;
        });
    }

    public function filterContent( $content )
    { 
        if ( is_page() && in_the_loop() && is_main_query() )
            return $this->filteredContent;
    
        return $content;
    }

    public function keys()
    {
        return array_keys($this->config);        
    }
    
    public function config( $key )
    {
        return $this->config[$key];
    }

    public function run()
    {
        add_action('get_header', [$this, 'filterBlocks']);        
    }

    protected function getBlocks()
    {
        return parse_blocks(get_the_content());
    }
}