<?php

add_action( 'wp_enqueue_scripts', 'porto_child_css', 1001 );

// Load CSS
function porto_child_css() {
	// porto child theme styles
	wp_deregister_style( 'styles-child' );
	wp_register_style( 'styles-child', esc_url( get_stylesheet_directory_uri() ) . '/style.css' );
	wp_enqueue_style( 'styles-child' );

	if ( is_rtl() ) {
		wp_deregister_style( 'styles-child-rtl' );
		wp_register_style( 'styles-child-rtl', esc_url( get_stylesheet_directory_uri() ) . '/style_rtl.css' );
		wp_enqueue_style( 'styles-child-rtl' );
	}
}



/// перевод wishlist
add_filter('gettext', 'translate_text');
add_filter('ngettext', 'translate_text');
function translate_text($translated) {
$translated = str_ireplace('Product name', 'Наименование', $translated);
$translated = str_ireplace('Информация о заказе', 'Ваш заказ', $translated);
$translated = str_ireplace('No products added to the wishlist', 'В закладки товаров не добавлено', $translated);
$translated = str_ireplace('Подытог', 'Итого', $translated);
$translated = str_ireplace('Apply Coupon', 'Применить купон', $translated);
$translated = str_ireplace('Консоль', 'Личный кабинет', $translated);
$translated = str_ireplace('RELATED PRODUCTS', 'Похожие товары', $translated);
$translated = str_ireplace('Детали', 'Дополнительная информация', $translated);
	
return $translated;
}


/// сортировка по акции
add_filter( 'woocommerce_get_catalog_ordering_args', 'wcs_get_catalog_ordering_args' );
function wcs_get_catalog_ordering_args( $args ) {
  $orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
    if ( 'on_sale' == $orderby_value ) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        $args['meta_key'] = '_sale_price';
    }
    return $args;
}
add_filter( 'woocommerce_default_catalog_orderby_options', 'wcs_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'wcs_catalog_orderby' );
function wcs_catalog_orderby( $sortby ) {
    $sortby['on_sale'] = 'Сортировка по акции';
    return $sortby;
}


/**
* Move WooCommerce subcategory list items into
* their own <ul> separate from the product <ul>.
*/
add_action( 'init', 'move_subcat_lis' );
function move_subcat_lis() {
	// Remove the subcat <li>s from the old location.
	remove_filter( 'woocommerce_product_loop_start', 'woocommerce_maybe_show_product_subcategories' );
	add_action( 'woocommerce_before_shop_loop', 'msc_product_loop_start', 1 );
	add_action( 'woocommerce_before_shop_loop', 'msc_maybe_show_product_subcategories', 2 );
	add_action( 'woocommerce_before_shop_loop', 'msc_product_loop_end', 3 );
}
/**
 * Conditonally start the product loop with a <ul> contaner if subcats exist.
 */
function msc_product_loop_start() {
	$subcategories = woocommerce_maybe_show_product_subcategories();
	if ( $subcategories ) {
		woocommerce_product_loop_start();
	}
}
/**
 * Print the subcat <li>s in our new location.
 */
function msc_maybe_show_product_subcategories() {
	echo woocommerce_maybe_show_product_subcategories();
}
/**
 * Conditonally end the product loop with a </ul> if subcats exist.
 */
function msc_product_loop_end() {
	$subcategories = woocommerce_maybe_show_product_subcategories();
	if ( $subcategories ) {
		woocommerce_product_loop_end();
	}
}


/*
add_action( 'woocommerce_before_shop_loop_item_title', 'shop_sku' );
function shop_sku(){
global $product;
	 $upc = ltrim($product->sku, '0');
     $upc = ltrim($upc, '-');
     $upc = ltrim($upc, '0');
echo '<div itemprop="productID" class="sku">Код: ' . $upc . '</div>';
}


add_action( 'woocommerce_template_single_meta', 'shop_sku1' );
function shop_sku1(){
global $product;
	 $up1 = ltrim($product->sku, '0');
     $upc1 = ltrim($upc1, '-');
     $upc1 = ltrim($upc1, '0');
echo '<div itemprop="productID" class="sku">Код: ' . $upc1 . '</div>';
}

*/

/*
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20);
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 10);
function HB_woocommerce_template_dimensions(){ //Добавим функцию вызова панельки с размерами и весом
global $woocommerce, $post, $product;
$product->list_attributes();
}
add_action( 'woocommerce_single_product_summary', 'HB_woocommerce_template_dimensions', 15);
*/



add_filter( 'woocommerce_cart_item_name', 'showing_sku_in_cart_items', 99, 3 );
function showing_sku_in_cart_items( $item_name, $cart_item, $cart_item_key  ) {
    // The WC_Product object
    $product = $cart_item['data'];
    // Get the  SKU
    $sku = $product->get_sku();

    // When sku doesn't exist
    if(empty($sku)) return $item_name;
	
	//Clean leading zero
	$sku = ltrim($sku, '0');
	$sku = ltrim($sku, '-');
    $sku = ltrim($sku, '0');

    // Add the sku
    $item_name .= '<br><small class="product-sku">' . __( "Код: ", "woocommerce") . $sku . '</small>';
    return $item_name;
}




/**
 * Show product thumbnail on checkout page.
 *
 * @see {templates|woocommerce}/checkout/review-order.php
 **/
add_filter( 'woocommerce_cart_item_name', 'jfs_checkout_show_product_thumbnail', 10, 2 );
function jfs_checkout_show_product_thumbnail( $name, $cart_item ) {
    if ( ! is_checkout() ) return $name;
    $thumbnail = '<span class="product-name__thumbnail" style="float: left; padding-right: 15px">' . get_the_post_thumbnail( $cart_item['product_id'], array( 60, 120 ) ) . '</span>';
    return $thumbnail . '<span class="product-name__text">' . $name . '</span>';
}




/**WC Отключение оплаты при оформлении**/
add_filter( 'woocommerce_cart_needs_payment', '__return_false' );


/**Удаление последней хлебной крошки Yoast
function adjust_single_breadcrumb( $link_output) {
	if(strpos( $link_output, 'breadcrumb_last' ) !== false ) {
		$link_output = '';
	}
   	return $link_output;
}
add_filter('wpseo_breadcrumb_single_link', 'adjust_single_breadcrumb' );
**/




/**Скрыть превью товаров если оно всего одно**/
add_action( 'woocommerce_product_thumbnails', 'enable_gallery_for_multiple_thumbnails_only', 5 );
function enable_gallery_for_multiple_thumbnails_only() {
    global $product;
    if( ! is_a($product, 'WC_Product') ) {
        $product = wc_get_product( get_the_id() );
    }
    if( empty( $product->get_gallery_image_ids() ) ) {
        remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
    }
}

/**Удаление нулей карточка**/
add_filter( 'woocommerce_product_get_sku', 'update_sku', 10, 1);
function update_sku( $sku ){
	$newsku = ltrim($sku, '0');
	$newsku = ltrim($newsku, '-');
	$newsku = ltrim($newsku, '0');
    return $newsku;
}



//убираем количество в категориях
add_filter('woocommerce_subcategory_count_html','remove_count');

function remove_count(){
 $html='';
 return $html;
}



//Disable Article Counter - query runs for about 1-2 seconds
        add_filter('admin_init', function () {
            foreach (get_post_types() as $type) {
                $cache_key = _count_posts_cache_key($type, "readable");
                $counts = array_fill_keys(get_post_stati(), 1);
                wp_cache_set($cache_key, (object)$counts, 'counts');
            }
        }, -1);
        add_action('admin_head', function () {
             $css  = '<style>';
             $css .= '.subsubsub a .count { display: none; }';
             $css .= '</style>';

             echo $css;
        });



/**
 * Change the placeholder image
 */
add_filter('woocommerce_placeholder_img_src', 'custom_woocommerce_placeholder_img_src');

function custom_woocommerce_placeholder_img_src( $src ) {
	$upload_dir = wp_upload_dir();
	$uploads = untrailingslashit( $upload_dir['baseurl'] );
	// replace with path to your image
	$src = $uploads . '/2020/05/woocommerce-placeholder-e1588812834373.jpg'; 
	return $src;
}

add_filter( 'wpseo_breadcrumb_single_link' ,'wpseo_remove_breadcrumb_link', 10 ,2);

function wpseo_remove_breadcrumb_link( $link_output , $link ){
    $text_to_remove = 'Товары';
  
    if( $link['text'] == $text_to_remove ) {
      $link_output = '';
    }
 
    return $link_output;
}



/**
add_filter( 'wpseo_breadcrumb_links', 'wpseo_breadcrumb_remove_postname' );
function wpseo_breadcrumb_remove_postname( $links ) {
	if( sizeof($links) > 1 ){
		array_pop($links);
	}
	return $links;
}
**/




/** Hide Download Tab Admin menu */
add_filter( 'woocommerce_account_menu_items', 'custom_remove_downloads_my_account', 999 );
 
function custom_remove_downloads_my_account( $items ) {
unset($items['downloads']);
return $items;
}




// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------


use Premmerce\Filter\Seo\SeoListener;
use Premmerce\Filter\Seo\SeoModel;

/**
 *
 */
function hangsys_add_description_at_top_open_buffer() {
   ob_start();
}

/**
 *
 */
function hangsys_add_description_at_top_clear_buffer() {
   ob_clean();
}


/**
 *
 * вывод верхнего описания для категории товаров через premmerce filter
 *
 */
function hangsys_add_description_at_top(){

	// если категория продукта и фильтр активен и нет пагинации
	if ( is_product_category() && is_filtered() && ! is_paged() ) {


		$path = parse_url( $_SERVER['REQUEST_URI'] )['path'];

		// находим в кастомной таблице premmerce filter нужное правило
		$seom = new seoModel();
		$rule = $seom
			->where( array( 'path' => trim( $path, '/' ), 'enabled' => 1 ) )
			->returnType( SeoModel::TYPE_ROW )
			->limit( 1 )
			->get();

		// if have results from DB
		if ( is_array( $rule ) ) {

			$seo_listener = new SeoListener();
			$description  = $seo_listener->parseVariables( $rule['description'] );

			$main = ( ! empty( $description ) ) ? get_extended( $description ) : $description;
			$main = ( is_array( $main ) && array_key_exists( 'main', $main ) )
				? $main['main'] : $description;

			?>

            <div class="term-description">
				<?php echo apply_filters( 'the_content', $main ); ?>
            </div>

			<?php
		}
	}

}


/**
 * вывод второго описания категории товаров через premmerce filter
 *
 */
function hangsys_add_description_at_bottom( ) {


	// если категория продукта и фильтр активен и нет пагинации
	if ( is_product_category() && is_filtered() && ! is_paged() ) {


		$path = parse_url( $_SERVER['REQUEST_URI'] )['path'];

		// находим в кастомной таблице premmerce filter нужное правило
		$seom = new seoModel();
		$rule = $seom
			->where( array( 'path' => trim( $path, '/' ), 'enabled' => 1 ) )
			->returnType( SeoModel::TYPE_ROW )
			->limit( 1 )
			->get();

		// if have results from DB
		if ( is_array( $rule ) ) {

			$seo_listener = new SeoListener();
			$description  = $seo_listener->parseVariables( $rule['description'] );

			$extended = ( ! empty( $description ) ) ? get_extended( $description ) : $description;
			$extended = ( is_array( $extended ) && array_key_exists( 'extended', $extended ) )
				? $extended['extended'] : $description;

			?>

            <div class="clearfix"></div>
            <div class="term-description term-description-bottom">
				<?php echo apply_filters( 'the_content', $extended ); ?>
            </div>

			<?php
		}
	}

}


/**
 * если найдены SEO правила premmerce filter:
 *      удаляем вывод второго описания категории
 *      добавляем вывод своего второго описания категории
 */
function hangsys_registerActionsForRule() {
	remove_action( 'woocommerce_after_shop_loop', 'wpm_product_cat_archive_add_meta' );
	add_action( 'woocommerce_after_shop_loop', 'hangsys_add_description_at_bottom', 50 );

	add_action( 'woocommerce_archive_description', 'hangsys_add_description_at_top_open_buffer', 1 );
	add_action( 'woocommerce_archive_description', 'hangsys_add_description_at_top_clear_buffer', 29 );
	add_action( 'woocommerce_archive_description', 'hangsys_add_description_at_top', 30 );

}

add_action( 'premmerce_filter_rule_found', 'hangsys_registerActionsForRule', 10, 1 );


// ----------------------------------------------------------------
// ----------------------------------------------------------------
// ----------------------------------------------------------------


/**
 * Create Alt and Title Image
 */
function change_attachement_image_attributes( $attr, $attachment ) {
	// Get post parent
	$parent = get_post_field( 'post_parent', $attachment );

	// Get post type to check if it's product
	$type = get_post_field( 'post_type', $parent );
	if ( $type != 'product' ) {
		return $attr;
	}

	/// Get title and alt
	$title = get_post_field( 'post_title', $parent );
	$attr['alt']   = $title . ' - Винные Желания';
	$attr['title'] = $title . ' - Винные Желания';

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'change_attachement_image_attributes', 20, 2 );


/**
 * Вывод списка атрибутов после хука "porto_woocommerce_single_product_summary2"
 */

add_action ( 'porto_woocommerce_single_product_summary2', 'show_attributes', 25 );
function show_attributes() {
  global $product;
  wc_display_product_attributes( $product );
}


add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
 
function woo_remove_product_tabs( $tabs ) {
 
# unset( $tabs['description'] ); // Убираем вкладку "Описание"
# unset( $tabs['reviews'] ); // Убираем вкладку "Отзывы"
unset( $tabs['additional_information'] ); // Убираем вкладку "Свойства"
return $tabs;
 
}

add_action( 'porto_woocommerce_single_product_summary2', 'shop_sku' );
function shop_sku(){
global $product;	
echo	'<table class="woocommerce-product-attributes shop_attributes table table-striped bolden"><tbody>
   <tr class="woocommerce-product-attributes-item woocommerce-product-attributes-item--attribute_pa_5e91ef2f080f6">
				<th class="woocommerce-product-attributes-item__label">Артикул</th>
				<td class="woocommerce-product-attributes-item__value"><p>' . $product->get_sku() . '</p>
</td></tr></tbody></table>';
	
}


/**
 * @snippet       Add new textarea to Product Category Pages - WooCommerce
 * @how-to        Get CustomizeWoo.com FREE
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.9
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */  
 
// ---------------
// 1. Display field on "Add new product category" admin page
 
add_action( 'product_cat_add_form_fields', 'bbloomer_wp_editor_add', 10, 2 );
 
function bbloomer_wp_editor_add() {
    ?>
    <div class="form-field">
        <label for="seconddesc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label>
       
      <?php
      $settings = array(
         'textarea_name' => 'seconddesc',
         'quicktags' => array( 'buttons' => 'em,strong,link' ),
         'tinymce' => array(
            'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
            'theme_advanced_buttons2' => '',
         ),
         'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
      );
 
      wp_editor( '', 'seconddesc', $settings );
      ?>
       
        <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
    </div>
    <?php
}
 
// ---------------
// 2. Display field on "Edit product category" admin page
 
add_action( 'product_cat_edit_form_fields', 'bbloomer_wp_editor_edit', 10, 2 );
 
function bbloomer_wp_editor_edit( $term ) {
    $second_desc = htmlspecialchars_decode( get_term_meta( $term->term_id, 'seconddesc', true ) );
    ?>
    <tr class="form-field">
        <th scope="row" valign="top"><label for="second-desc"><?php echo __( 'Second Description', 'woocommerce' ); ?></label></th>
        <td>
            <?php
          
         $settings = array(
            'textarea_name' => 'seconddesc',
            'quicktags' => array( 'buttons' => 'em,strong,link' ),
            'tinymce' => array(
               'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
               'theme_advanced_buttons2' => '',
            ),
            'editor_css' => '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
         );
 
         wp_editor( $second_desc, 'seconddesc', $settings );
         ?>
       
            <p class="description"><?php echo __( 'This is the description that goes BELOW products on the category page', 'woocommerce' ); ?></p>
        </td>
    </tr>
    <?php
}
 
// ---------------
// 3. Save field @ admin page
 
add_action( 'edit_term', 'bbloomer_save_wp_editor', 10, 3 );
add_action( 'created_term', 'bbloomer_save_wp_editor', 10, 3 );
 
function bbloomer_save_wp_editor( $term_id, $tt_id = '', $taxonomy = '' ) {
   if ( isset( $_POST['seconddesc'] ) && 'product_cat' === $taxonomy ) {
      update_woocommerce_term_meta( $term_id, 'seconddesc', esc_attr( $_POST['seconddesc'] ) );
   }
}
 
// ---------------
// 4. Display field under products @ Product Category pages 
 
add_action( 'woocommerce_after_shop_loop', 'bbloomer_display_wp_editor_content', 5 );
 
function bbloomer_display_wp_editor_content() {
   if ( is_product_taxonomy() ) {
      $term = get_queried_object();
      if ( $term && ! empty( get_term_meta( $term->term_id, 'seconddesc', true ) ) ) {
         echo '<p class="term-description">' . wc_format_content( htmlspecialchars_decode( get_term_meta( $term->term_id, 'seconddesc', true ) ) ) . '</p>';
      }
   }
}