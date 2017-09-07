<?php
/*
Plugin Name: cf-user
Plugin URI: 
Description: 
Version: 1.0.0
Author:
Author URI: 
License: GPLv2
*/


/**
** A base module for the following types of tags:
** 	[firstname] and [firstname*]		# First name
** 	[lastname] and [lastname*]		# Last name
**/

/* form_tag handler */

add_action( 'wpcf7_init', 'wpcf7_add_form_tag_firstname' );

function wpcf7_add_form_tag_firstname() {
	wpcf7_add_form_tag( array( 'firstname', 'firstname*', 'lastname', 'lastname*' ),
		'wpcf7_firstname_form_tag_handler', array( 'name-attr' => true ) );
}




function wpcf7_firstname_form_tag_handler( $tag ) {
	if ( empty( $tag->name ) ) {
		return '';
	}
        $firstname = $lastName = "";
        $current_user = wp_get_current_user();
        if($current_user->ID > 0 ){
            $firstname = $current_user->user_firstname;
            $lastName = $current_user->user_lastname;
        }
        
	$validation_error = wpcf7_get_validation_error( $tag->name );

	$class = wpcf7_form_controls_class( $tag->type );

	$class .= ' wpcf7-validates-as-firstname';

	if ( $validation_error ) {
		$class .= ' wpcf7-not-valid';
	}

	$atts = array();

	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_id_option();
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'signed_int', true );
	

	if ( $tag->has_option( 'readonly' ) ) {
		$atts['readonly'] = 'readonly';
	}

	if ( $tag->is_required() ) {
		$atts['aria-required'] = 'true';
	}

	$atts['aria-invalid'] = $validation_error ? 'true' : 'false';

	$value = (string) reset( $tag->values );
        
     
         
	if ( $tag->has_option( 'placeholder' ) ) {
            $atts['placeholder'] = $value;
            if($tag->type == "firstname"){
                $value = $firstname;
            }else{
                $value = $lastName;
            }
	}

	$value = $tag->get_default_option( $value );

	$value = wpcf7_get_hangover( $tag->name, $value );

	$atts['value'] = $value;

	if ( wpcf7_support_html5() ) {
		$atts['type'] = $tag->basetype;
	} else {
		$atts['type'] = 'text';
	}

	$atts['name'] = $tag->name;

	$atts = wpcf7_format_atts( $atts );

	$html = sprintf(
		'<span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
		sanitize_html_class( $tag->name ), $atts, $validation_error );

	return $html;
}


/* Validation filter */

add_filter( 'wpcf7_validate_firstname', 'wpcf7_firstname_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_firstname*', 'wpcf7_firstname_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_lastname', 'wpcf7_firstname_validation_filter', 10, 2 );
add_filter( 'wpcf7_validate_lastname*', 'wpcf7_firstname_validation_filter', 10, 2 );

function wpcf7_firstname_validation_filter( $result, $tag ) {
	$name = $tag->name;

	$value = isset( $_POST[$name] )
		? trim( strtr( (string) $_POST[$name], "\n", " " ) )
		: '';

	if ( $tag->is_required() && '' == $value ) {
		$result->invalidate( $tag, wpcf7_get_message( 'invalid_required' ) );
	}

	return $result;
}


/* Messages */

add_filter( 'wpcf7_messages', 'wpcf7_firstname_messages' );

function wpcf7_firstname_messages( $messages ) {
	return array_merge( $messages, array(
		'invalid_firstname' => array(
			'description' => __( "Number format that the sender entered is invalid", 'contact-form-7' ),
			'default' => __( "The firstname format is invalid.", 'contact-form-7' )
		)
	) );
}


/* Tag generator */

add_action( 'wpcf7_admin_init', 'wpcf7_add_tag_generator_firstname', 18 );

function wpcf7_add_tag_generator_firstname() {
	$tag_generator = WPCF7_TagGenerator::get_instance();
	$tag_generator->add( 'user', __( 'USER', 'contact-form-7' ),
		'wpcf7_tag_generator_firstname' );
}

function wpcf7_tag_generator_firstname( $contact_form, $args = '' ) {
	$args = wp_parse_args( $args, array() );
	$type = 'firstname';

	$description = __( "Generate a form-tag for a field for numeric value input. For more details, see %s.", 'contact-form-7' );

	$desc_link = wpcf7_link( __( 'https://contactform7.com/firstname-fields/', 'contact-form-7' ), __( 'Number Fields', 'contact-form-7' ) );

?>
<div class="control-box">
<fieldset>
<legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>

<table class="form-table">
<tbody>
	<tr>
	<th scope="row"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></th>
	<td>
		<fieldset>
		<legend class="screen-reader-text"><?php echo esc_html( __( 'Field type', 'contact-form-7' ) ); ?></legend>
		<select name="tagtype">
			<option value="firstname" selected="selected"><?php echo esc_html( __( 'First Name', 'contact-form-7' ) ); ?></option>
			<option value="lastname"><?php echo esc_html( __( 'Last Name', 'contact-form-7' ) ); ?></option>
		</select>
		<br />
		<label><input type="checkbox" name="required" /> <?php echo esc_html( __( 'Required field', 'contact-form-7' ) ); ?></label>
		</fieldset>
	</td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-name' ); ?>"><?php echo esc_html( __( 'Name', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="name" class="tg-name oneline" id="<?php echo esc_attr( $args['content'] . '-name' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-values' ); ?>"><?php echo esc_html( __( 'Default value', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="values" class="oneline" id="<?php echo esc_attr( $args['content'] . '-values' ); ?>" /><br />
	<label><input type="checkbox" name="placeholder" class="option" /> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'contact-form-7' ) ); ?></label></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-id' ); ?>"><?php echo esc_html( __( 'Id attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="id" class="idvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-id' ); ?>" /></td>
	</tr>

	<tr>
	<th scope="row"><label for="<?php echo esc_attr( $args['content'] . '-class' ); ?>"><?php echo esc_html( __( 'Class attribute', 'contact-form-7' ) ); ?></label></th>
	<td><input type="text" name="class" class="classvalue oneline option" id="<?php echo esc_attr( $args['content'] . '-class' ); ?>" /></td>
	</tr>
</tbody>
</table>
</fieldset>
</div>

<div class="insert-box">
	<input type="text" name="<?php echo $type; ?>" class="tag code" readonly="readonly" onfocus="this.select()" />

	<div class="submitbox">
	<input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7' ) ); ?>" />
	</div>

	<br class="clear" />

	<p class="description mail-tag"><label for="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>"><?php echo sprintf( esc_html( __( "To use the value input through this field in a mail field, you need to insert the corresponding mail-tag (%s) into the field on the Mail tab.", 'contact-form-7' ) ), '<strong><span class="mail-tag"></span></strong>' ); ?><input type="text" class="mail-tag code hidden" readonly="readonly" id="<?php echo esc_attr( $args['content'] . '-mailtag' ); ?>" /></label></p>
</div>
<?php
}
