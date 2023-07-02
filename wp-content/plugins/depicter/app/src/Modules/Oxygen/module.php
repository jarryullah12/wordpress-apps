<?php

namespace Depicter\Modules\Oxygen;

class Module extends \OxyEl
{

    public function init() {
        if ( isset( $_GET['ct_builder'] ) ) {
            add_action( 'wp_enqueue_scripts', [ $this, 'enqueueDepicterAssets' ] );
        }
    }

    public function enqueueDepicterAssets() {
        \Depicter::front()->assets()->enqueueStyles();
		\Depicter::front()->assets()->enqueueScripts();
    }

    // Define the element's name.
    public function name() {
        return __("Depicter", 'depicter');
    }

    // Element options
    public function options(){

        return array(
            //"wrapper_class" => $classes,
            "server_side_render" => true
        );

    }

    public function render( $options, $defaults, $content ){

        if ( !empty( $options['slider_id'] ) ) {
			echo \Depicter::front()->render()->document( $options['slider_id'] );
            if ( isset( $_GET['action'] ) && $_GET['action'] == 'oxy_render_oxy-depicter' ) {
                echo "<script>Depicter.initAll();</script>";
            }
		} else {
			echo esc_html__('Please select a Depicter slider','depicter' );
		}

    }

    public function getSlidersList() {
		$list = [
			0 => __( 'Select Slider', 'depicter' )
		];
		$documents = \Depicter::documentRepository()->select( ['id', 'name'] )->orderBy('modified_at', 'DESC')->findAll()->get();
		$documents = $documents ? $documents->toArray() : [];
		foreach( $documents as $document ) {
			$list[ $document['id'] ] = "[#{$document['id']}]: " . $document['name'];
		}
		return $list;
	}

    public function controls(){

        // Select Slider
        $this->addOptionControl(
            array(
                "type" => 'dropdown',
                "name" => __("Select Slider", 'depicter' ),
                "slug" => 'slider_id',
            )
        )->setValue( $this->getSlidersList() )->rebuildElementOnChange();
    }

}

new Module();
