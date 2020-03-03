<?php 
/**
 * Plugin Name: LifterLms Reporting
 * Description: Plugin to generate reporting view on frontend
 * Plugin URI: http://#
 * Author: Mohammad Karrar
 * Author URI: http://#
 * Version: 1.0.0
 * License: GPL2
 * Text Domain: text-domain
 * Domain Path: domain/path
 */


// load styles and scripts
function cr_load_assets() {
    // enqueue stylesheet for plugin
    wp_enqueue_style( 'sr_stylesheet', plugin_dir_url( __FILE__ ) . 'assets/cr_style.css', false, 'all' );
    
    // enqueue script for plugin
    wp_enqueue_script( 'sr_script', plugin_dir_url( __FILE__ ) . 'assets/cr_script.js', false, false );
}


function LifterLMS_Reporting() {

    // stylesheet and scripts
    add_action( 'wp_enqueue_scripts', 'cr_load_assets' );
    
    // shortcode here
    require 'includes/shortcode/shortcode-course-reporting.php';


    // lifterlms plugin directory path
    $llms = LLMS()->plugin_path();
    
    // lifter lms classes 
    require_once  $llms . '\includes\admin\reporting\tables\llms.table.course.students.php';
    require_once  $llms . '\includes\admin\reporting\tables\llms.table.student.courses.php';
    require_once  $llms . '\includes\admin\reporting\tables\llms.table.courses.php';   
    require_once  $llms . '\includes\class.llms.course.data.php';
    require_once  $llms . '\includes\admin\reporting\class.llms.admin.reporting.php';


}

// plugin loaded
add_action( 'plugins_loaded', 'LifterLMS_Reporting');
