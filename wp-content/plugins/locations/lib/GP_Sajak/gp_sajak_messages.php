<?php

if ( !class_exists('GP_Sajak_Messages') ):

	class GP_Sajak_Messages
	{
		var $messages = array();
		var $key = '';
		
		function __construct( $key = '' )
		{
			if ( empty($key) ) {
				$key = 'gp_sajak_messages';
			}
			$this->key = $key . '_queue';
			$this->add_hooks();
		}
		
		function add_hooks()
		{
			add_action( 'admin_notices', array($this, 'show_admin_notices') );
		}
		
		function show_admin_notices()
		{
			$messages = $this->get_queue();
			if( !empty($messages) ){
				foreach($messages as $item){
					echo wp_kses( $this->format_message( $item ), 'post' );
				}
			}			
		}		
		
		function add( $message, $class = '' )
		{
			$new_item = compact( 'message', 'class' );
			$this->messages[] = $new_item;			
			$this->store_queue();
			$this->restore_queue();

		}
		
		function get_all()
		{
			$this->restore_queue();
			return $this->messages;
		}
		
		function get_queue()
		{
			$q = $this->get_all();			
			$this->flush_queue();
			return $q;
		}
				
		function format_message( $item )
		{
			$tmpl = '<div class="notice notice-success is-dismissible flash %s"><p>%s</p></div>';
			return sprintf($tmpl, $item['class'], $item['message']);
		}
		
		function store_queue()
		{
			set_transient($this->key, $this->messages);
		}

		function restore_queue()
		{
			$q = get_transient($this->key);			
			if ( empty($q) || !is_array($q) ) {
				$q = array();
			}
			$this->messages = $q;
		}		

		function flush_queue()
		{
			delete_transient($this->key);
			$this->messages = array();
		}
	}
	
endif;//class_exists