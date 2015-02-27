<?php
ob_start();
/**
 * Plugin Name: WP Category Management
 * Description: This plugin Provides an UI Interface to easily associate categories/subcategories to sidebar category links
 * Version: 1.0.0
 * Author: Harini
 */
add_action('admin_menu', 'wp_category_management');

#--------------------------------------------------------------------
# Adding Admin menu page in backend
#---------------------------------------------------------------------

function wp_category_management() 
{
	add_menu_page('Wp-Category-Management Settings', 'Wp-Category-Management', 'administrator', 'wp_category_managementsettings', 'wp_category_managementsettings_page', 'dashicons-admin-generic');
}

function wp_category_managementsettings_page() 
{
	global $wpdb;
	$table_name = $wpdb->prefix . "categorymanagement_list";
	$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name");
	 if (isset($_POST['save']))
	 {
		$cat='';
		for($i=0;$i<count($_POST['cat']);$i++)
		{
			if($i == (count($_POST['cat'])-1))
			{
				$cat.= $_POST['cat'][$i];
			}
			else
			{
				$cat.= $_POST['cat'][$i].',';
			}
		}

	if(empty($retrieve_data))
	{
		$wpdb->insert($table_name,array('category_list' => $cat,'front_page'=>$_POST['frontpage'],'sp_page'=>$_POST['sppage'],'c_pages'=>$_POST['cpages']));
	}
	else
	{
		$wpdb->update($table_name, array('category_list' => $cat,'front_page'=>$_POST['frontpage'],'sp_page'=>$_POST['sppage'],'c_pages'=>$_POST['cpages']),array('id' => 1 ));
		$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name");
	} 
		
?>
			<div class="updated"> 
				<p><strong>Settings saved.</strong></p>
			</div>
<?php
        }	
	if(!empty($retrieve_data))
		{
			$catarray=explode(",",$retrieve_data[0]->category_list);
		}
		else
		{
			$catarray=array();
		}
	 
	wp_enqueue_script(array('jquery','jquery-ui-widget','jquery-ui-sortable'));	            //Adding js files
	$args = array("hide_empty" => 0,"orderby" => "name","order" => "ASC" );
	$categories =  get_categories($args);							   //Getting Categories
?>
	<div class="wrap">
	<h2>Wp-Category-Management Settings</h2>
	<div id="total" style="min-height:300px">
	<div  id="widgets-left" class="widget-liquid-left">
		<form method="post">
		<p class="description">Please check the Categories/Sub-categories which you want to display in sidebar.
		<br>You can also reorder the Categories/Sub-categories.</p><br>

		<div class="catdiv">
	<?php
		foreach ($categories as $cat) 
		{
	?>		
				<div class="widget">
					<div class="widget-top" style="width:300px">
						<div class="widget-title">
							<h4><input type="checkbox" name="cat[]" value="<?=$cat->cat_ID;?>" <?php if(array_search($cat->cat_ID,$catarray) !== false ) : ?> checked="checked" <?php endif; ?> ><?=$cat->cat_name;?></h4>
						</div>
					 </div>
				 </div>
	<?php
		}
		?>
		</div>
	</div>   

	<div  id="widgets-left" class="widget-liquid-right">
			<p class="description">Please check the pages where you want to display specifically the <br> Categories/Sub-categories list.<br>The widget is displayed only on the pages which you have checked</p>
			<div class="widget">
			<div class="widget-top" style="width:300px">
							<div class="widget-title">
								<h4><input type="checkbox" name="frontpage" value="1" <?php if($retrieve_data[0]->front_page == 1 ) : ?> checked="checked" <?php endif; ?>>Front Page</h4>
							</div>
			 </div>
			 </div>
			<div class="widget">
			 <div class="widget-top" style="width:300px">
							<div class="widget-title">
								<h4><input type="checkbox" name="sppage" value="1" <?php if($retrieve_data[0]->sp_page == 1 ) : ?> checked="checked" <?php endif; ?> >Single Post Page</h4>
							</div>
			 </div>
			 </div>
			 <div class="widget-top" style="width:300px">
							<div class="widget-title">
								<h4><input type="checkbox" name="cpages" value="1" <?php if($retrieve_data[0]->c_pages == 1 ) : ?> checked="checked" <?php endif; ?> >Category Pages</h4>
							</div>
			 </div>
			 </div>
	</div> 
	</div> 
	<input name="save" type="submit" class="button button-primary button-large" value="Save">
	</form>
	</div>
<script>

jQuery(document).ready(function()
{
	//jQuery(".catdiv").draggable();
	jQuery(".catdiv").sortable({cursor: 'move'});
   
});
</script>
<?php
}

#--------------------------------------------------------------------
# Installing the tables during plugin Installation
#---------------------------------------------------------------------


function categorymanagement_list_install() 
{
	global $wpdb;
	ob_start();
	$table_name = $wpdb->prefix . "categorymanagement_list";
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) 
	{
	    $sql = 'CREATE TABLE ' . $table_name . ' (
	    id int(11) NOT NULL AUTO_INCREMENT,
	    category_list VARCHAR(255)  NULL,
	    front_page int(11)  NULL,
	    sp_page int(11)  NULL,
	    c_pages int(11)  NULL,
	    PRIMARY KEY (id))';
	    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 				//reference to upgrade.php file
	    dbDelta( $sql );
	}	
	//$contents = ob_get_contents();
}

register_activation_hook( __FILE__,'categorymanagement_list_install' );

#--------------------------------------------------------------------
# Creating widget for the sidebar
#---------------------------------------------------------------------
class wp_category_management_widget extends WP_Widget {
 
        // constructor
        function wp_category_management_widget() 
	{
               parent::WP_Widget(false, $name = __('Wp Category Management', 'wp_category_management_plugin') );
        }
 
        // widget form creation
        function form($instance) 
	{      
		if($instance) 
		{
		     $title = esc_attr($instance['title']);
		} 
		else 
		{
		     $title = '';
		}
	   ?>
		<p>
		    <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_category_management_plugin'); ?></label>
		    <input class="customclass" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
		</p>
<?php
        }
 
        // widget update
        function update($new_instance, $old_instance) 
	{
                $instance = $old_instance;
	        $instance['title'] = strip_tags($new_instance['title']);
	        return $instance;
        }
 
        // widget display
        function widget($args, $instance) 
	{
                extract( $args );
	        // these are the widget options
	        $title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		global $wpdb;
		$table_name = $wpdb->prefix . "categorymanagement_list";
		$retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name");
	   	echo '<div class="widget-text widget_categories">';
		if($retrieve_data[0]->category_list != '')
		{
			//echo "ddds";
			//$show=1;
			if(($retrieve_data[0]->front_page == 0) && ($retrieve_data[0]->sp_page  == 0) && ($retrieve_data[0]->c_pages == 0))
			{
				$show=1;
			}
			else
			{
				$show=showwidget($retrieve_data);
			}
			if($show == 1)
			{
			   	if ($title) 
				{
			      		echo '<h2 class="widget-title">'.$title.'</h3>';
			   	}
				echo '<ul>';
				$catarray=explode(",",$retrieve_data[0]->category_list);
				for($i=0; $i<count($catarray);$i++)
				{
				$category = get_category($catarray[$i]);
				$catlink= get_category_link($catarray[$i]); 
				echo '<li class="cat-item cat-item-1"><a href="'.$catlink.'">'.$category->name.'</a>
	</li>';
				}
				echo '</ul>';
			}
		}
		echo '</div>';
   		echo $after_widget;

        }
}
 
#--------------------------------------------------------------------
# Register and load the widget
#---------------------------------------------------------------------

add_action('widgets_init', create_function('', 'return register_widget("wp_category_management_widget");'));

#--------------------------------------------------------------------
# function to uninstall the plugin and delete the tables
#---------------------------------------------------------------------

function wp_category_management_Uninstall()
{
  global $wpdb;
  $thetable = $wpdb->prefix."categorymanagement_list";
  $wpdb->query("DROP TABLE IF EXISTS $thetable");
}

#--------------------------------------------------------------------
# Registering uninstallation hook
#---------------------------------------------------------------------

register_uninstall_hook(__FILE__,'wp_category_management_Uninstall');

#--------------------------------------------------------------------
# function to check the current page status and display the widget
#---------------------------------------------------------------------

function showwidget($retrieve_data)
{
     $checkfront = is_front_page();
     if(is_front_page() == 1)
     {
	$fpage=$retrieve_data[0]->front_page;
	if($fpage== 1)
	{
		$show=1;
	}
	else
	{
		$show=0;
	}
     }
     else if(is_singular('post') == 1)
     {
	
	$spage=$retrieve_data[0]->sp_page;
	if($spage== 1)
	{
				$show=1;
	}
	else
	{
				$show=0;
	}
     }
     else if(is_category() == 1)
     {
	$cpages=$retrieve_data[0]->c_pages;
	if($cpages== 1)
	{
				$show=1;
	}
	else
	{
				$show=0;
	}
     }
     return $show;
}
//trigger_error(ob_get_contents(),E_USER_ERROR);
?>




