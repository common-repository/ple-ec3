<?php
//-----------------------------------------------------------------------------
/*
Plugin Name: PostLists-Extension EC3
Version: 2.1.1
Plugin URI: http://www.rene-ade.de/inhalte/wordpress-plugin-postlists-extension-ec3.html
Description: PostLists Extension for Event Calendar 3 provides event calendar placeholders, conditions and order options
Author: Ren&eacute; Ade
Author URI: http://www.rene-ade.de
*/
//-----------------------------------------------------------------------------
?>
<?php

//-----------------------------------------------------------------------------

// get placeholder value for shedule
function ple_ec3_getsheduleplaceholdervalue( $name, $shedule_start, $shedule_end ) {

 // placeholders
 switch( $name ) {
  case 'ec3_shedule_start': 
    return mysql2date( get_option('date_format'), $shedule_start ).
           ' '.
           mysql2date( get_option('time_format'), $shedule_start );                       
  case 'ec3_shedule_start_date': 
    return mysql2date( get_option('date_format'), $shedule_start );                   
  case 'ec3_shedule_start_time':
    return mysql2date( get_option('time_format'), $shedule_start );
  case 'ec3_shedule_end': 
    return mysql2date( get_option('date_format'), $shedule_end ).
           ' '.
           mysql2date( get_option('time_format'), $shedule_end );   
  case 'ec3_shedule_end_date':  
    return mysql2date( get_option('date_format'), $shedule_end );  
  case 'ec3_shedule_end_time':    
    return mysql2date( get_option('time_format'), $shedule_end );
  case 'ec3_shedule': 
    if( $shedule_start == $shedule_end ) {
      return mysql2date( get_option('date_format'), $shedule_start ).
             ' '.
             mysql2date( get_option('time_format'), $shedule_start );                           
    }
    if(    mysql2date(get_option('date_format'),$shedule_start)
        == mysql2date(get_option('date_format'),$shedule_end) ) {
      return mysql2date( get_option('date_format'), $shedule_start ).
             ' '.
             mysql2date( get_option('time_format'), $shedule_start ).
             ' '.
             __( 'to', 'ec3' ).
             ' '.
             mysql2date( get_option('time_format'), $shedule_end ); 
    }
    return mysql2date( get_option('date_format'), $shedule_start ).
           ' '.
           mysql2date( get_option('time_format'), $shedule_start ).
           ' '.
           __( 'to', 'ec3' ).
           ' '.
           mysql2date( get_option('date_format'), $shedule_end ).
           ' '.
           mysql2date( get_option('time_format'), $shedule_end );   
  case 'ec3_shedule_date': 
    if( $shedule_start == $shedule_end ) {
      return mysql2date( get_option('date_format'), $shedule_start );
    }
    return mysql2date( get_option('date_format'), $shedule_start ).
           ' '.
           __( 'to', 'ec3' ).
           ' '.
           mysql2date( get_option('date_format'), $shedule_end );   
  case 'ec3_shedule_time': 
    if( $shedule_start == $shedule_end ) {
      return mysql2date( get_option('time_format'), $shedule_start );
    }
    return mysql2date( get_option('time_format'), $shedule_start ).
           ' '.
           __( 'to', 'ec3' ).
           ' '.
           mysql2date( get_option('time_format'), $shedule_end );               
  }
  
  // default
  return null;
}

// replace placeholders
function ple_ec3_placeholdervalue( $value, $name, $args, $posts, $post ) {
  if( !in_array($name,ple_ec3_placeholders(array(),$post,$args)) )
    return $value;
    
  // get globals
  global $ec3, $wpdb;
  if( !$ec3 || !$wpdb )
    return false;
  
  // use query result if posts per event
  if( array_key_exists('ple_ec3_postperevent',$args) && $args['ple_ec3_postperevent'] ) {
    // use the assigned event
    return ple_ec3_getsheduleplaceholdervalue( $name, $post->ec3_start, $post->ec3_end );
  }
  
  // query shedule
  $query = 'SELECT DISTINCT start, end '.
           'FROM '.$ec3->schedule.' '.
           'WHERE post_id='.$post->ID;
  $shedulelist = $wpdb->get_results( $query );               
  if( !$shedulelist || count($shedulelist)<=0 )
    return null;
    
  // get placeholder value
  $value = array();
  foreach( $shedulelist as $shedule ) {
    $value[] = ple_ec3_getsheduleplaceholdervalue( $name, $shedule->start,  $shedule->end );
  }
  // get string of all values
  if( !empty($args['ple_ec3_separator']) )
    $value = implode( $args['ple_ec3_separator'], $value );
  else
    $value = $value[0];
  
  // default
  return $value;
}

//-----------------------------------------------------------------------------

// register placeholders
function ple_ec3_placeholders( $placeholders, $post, $args ) {

  // only post placeholders
  if( !$post )
    return $placeholders;
    
  // add placeholders
  $placeholders[] = 'ec3_shedule';
  $placeholders[] = 'ec3_shedule_date';
  $placeholders[] = 'ec3_shedule_time';
  $placeholders[] = 'ec3_shedule_start';
  $placeholders[] = 'ec3_shedule_start_date';
  $placeholders[] = 'ec3_shedule_start_time';
  $placeholders[] = 'ec3_shedule_end';
  $placeholders[] = 'ec3_shedule_end_date';
  $placeholders[] = 'ec3_shedule_end_time';

  return $placeholders;
}

// get placeholder description
function ple_ec3_placeholderdescription( $description, $placeholdername, $inpost ) {

  // only inpost placeholders
  if( !$inpost )
    return $description;
    
  // return description
  switch( $placeholdername ) {
    case 'ec3_shedule':
      return 'the event calendar start-datetime and the end-datetime';
    case 'ec3_shedule_date':
      return 'the event calendarstart-date and the end-date';
    case 'ec3_shedule_time':
      return 'the event calendarstart-time and the end-time';
    case 'ec3_shedule_start':
      return 'the event calendarstart-datetime';
    case 'ec3_shedule_start_date':
      return 'the event calendarstart-date';
    case 'ec3_shedule_start_time':
      return 'the event calendarstart-time';
    case 'ec3_shedule_end':
      return 'the event calendarend-datetime';
    case 'ec3_shedule_end_date':
      return 'the event calendarend-date';
    case 'ec3_shedule_end_time':
      return 'the event calendarend-time';
  }

  // keep
  return $description;
}

//-----------------------------------------------------------------------------

function ple_ec3_fields( $fields ) {

  // get types
  $types = pl_admin_data_getfields_gettypes();

  // add admin fields
  $fields['ple_ec3_separator'] = array(
    'description'=>'Separator for multiple events in ec3-placeholders',
    'type'=>'',
    'expert'=>false
  );
  $fields['ple_ec3_only'] = array(
    'description'=>'Show only posts with events',
    'type'=>array(''=>'','Yes'=>1),
    'expert'=>false
  );
  $description_onlyeventposts = '<br>(only posts with one or more events will be shown)';
  $fields['ple_ec3_postperevent'] = array( 
    'description'=>'Show separate post entry for each event'.$description_onlyeventposts,
    'type'=>$types['boolean'],
    'expert'=>false
  );
  $fields['ple_ec3_start'] = array( 
    'description'=>'Show only posts with event start time'.$description_onlyeventposts,
    'type'=>array(''=>'','In the future'=>'future','In the past'=>'past'),
    'expert'=>false
  );
  $fields['ple_ec3_end'] = array( 
    'description'=>'Show only posts with event end time'.$description_onlyeventposts,
    'type'=>array(''=>'','In the future'=>'future','In the past'=>'past'),
    'expert'=>false
  );

  // add orderby values
  $fields['orderby']['type']['EC3 Start (only posts with events will be shown)'] = 'ec3_start';
  $fields['orderby']['type']['EC3 End (only posts with events will be shown)']   = 'ec3_end';
  
  // return fields
  return $fields;
}
  
//-----------------------------------------------------------------------------

function ple_ec3_args( $args ) {

  // check if used
  if( !(array_key_exists('ple_ec3_postperevent',$args)&&$args['ple_ec3_postperevent']) &&
      !(array_key_exists('ple_ec3_only',$args)&&$args['ple_ec3_only']) &&
      !($args['orderby']=='ec3_start'||$args['orderby']=='ec3_end') &&
      !(array_key_exists('ple_ec3_start',$args)&&$args['ple_ec3_start']) &&
      !(array_key_exists('ple_ec3_end',$args)&&$args['ple_ec3_end']) )
    return $args;
 
  // copy args to clean
  $args_clean = $args;
  
  // check if orderby contains ec3 col
  if( $args['orderby']=='ec3_start' || $args['orderby']=='ec3_end' ) {
    // clean args for error return
    $args_defaults = pl_getposts_getdefaults();
    $args_clean['orderby'] = $args_defaults['orderby'];
  }
      
  // get globals
  global $ec3, $wpdb;
  if( !$ec3 || !$wpdb )
    return $args_clean;      
      
  // add table load
  if( !empty($args['load']) )
    $args['load'] .= ', ';
  $args['load'] .= $ec3->schedule;
  
  // add relation
  if( !empty($args['where']) )
    $args['where'] .= ' AND ';
  $args['where'] .= $ec3->schedule.'.post_id=ID';
  
  // if post per event is set or orderby/conditions needs other grouping
  if( (array_key_exists('ple_ec3_postperevent',$args)&&$args['ple_ec3_postperevent']) 
      || ($args['orderby']=='ec3_start'||$args['orderby']=='ec3_end')
      || (array_key_exists('ple_ec3_start',$args)&&$args['ple_ec3_start']) 
      || (array_key_exists('ple_ec3_end',$args)&&$args['ple_ec3_end']) ) {  
    // add new grouping              
    $args['groupby'] = str_replace( 
      $wpdb->posts.'.ID', 
      $ec3->schedule.'.sched_id', 
      $args['groupby'] );

    // if not post per event: remove limit (will be calculated later)
    if( !array_key_exists('ple_ec3_postperevent',$args) || !$args['ple_ec3_postperevent'] ) {
      $args['ple_ec3_numberposts'] = $args['numberposts'];  
      $args['numberposts'] = null;
    }
  }

  // if orderby, a condition, or post per event is used
  if( ($args['orderby']=='ec3_start'||$args['orderby']=='ec3_end')
      || (array_key_exists('ple_ec3_postperevent',$args)&&$args['ple_ec3_postperevent'])
      || (array_key_exists('ple_ec3_start',$args)&&$args['ple_ec3_start'])
      || (array_key_exists('ple_ec3_end',$args)&&$args['ple_ec3_end']) ) {
   // add aliases
    if( !empty($args['select']) )
      $args['select'] .= ', ';
    $args['select'] .= $ec3->schedule.'.start AS ec3_start, '
                      .$ec3->schedule.'.end AS ec3_end ';
  }
               
  // add conditions
  if( array_key_exists('ple_ec3_start',$args) && $args['ple_ec3_start'] ) {
    if( !empty($args['where']) )
      $args['where'] .= ' AND ';
    switch( $args['ple_ec3_start'] ) {
      case 'future':
        $args['where'] .= $ec3->schedule.'.start > \''.ec3_strftime('%Y-%m-%d %H:%M:%S').'\'';
      break;
      case 'past':
        $args['where'] .= $ec3->schedule.'.start < \''.ec3_strftime('%Y-%m-%d %H:%M:%S').'\'';
      break;
      default:
        $args['where'] .= '1';
      break;
    }
  }
  if( array_key_exists('ple_ec3_end',$args) && $args['ple_ec3_end'] ) {
    if( !empty($args['where']) )
      $args['where'] .= ' AND ';
    switch( $args['ple_ec3_end'] ) {
      case 'future':
        $args['where'] .= $ec3->schedule.'.end > \''.ec3_strftime('%Y-%m-%d %H:%M:%S').'\'';
      break;
      case 'past':
        $args['where'] .= $ec3->schedule.'.end < \''.ec3_strftime('%Y-%m-%d %H:%M:%S').'\'';
      break;
      default:
        $args['where'] .= '1';
      break;
    }      
  }

  // return args                  
  return $args;                  
}

//-----------------------------------------------------------------------------

function ple_ec3_posts( $posts, $args ) {
  
  // get globals
  global $ec3;
  if( !$ec3 )
    return $posts;
    
  // check if ec3 grouping used
  if( strpos($args['groupby'],$ec3->schedule.'.sched_id')===FALSE )
    return $posts;
  
  // check if multiple posts are allowed
  if( array_key_exists('ple_ec3_postperevent',$args) && $args['ple_ec3_postperevent'] ) 
    return $posts;
  
  // make posts distinct
  $posts_distinct = array();
  $posts_distinct_ids = array();  
  foreach( $posts as $post ) {
    if( !in_array($post->ID,$posts_distinct_ids) ) {
      $posts_distinct[] = $post;
      $posts_distinct_ids[] = $post->ID;
    }
  }

  // limit posts
  if( array_key_exists('ple_ec3_numberposts',$args) && $args['ple_ec3_numberposts'] ) 
    $posts_distinct = array_slice( $posts_distinct, 0, $args['ple_ec3_numberposts'] );

  // return distinct posts
  return $posts_distinct;
}

//-----------------------------------------------------------------------------

// postlists filters
add_filter( 'ple_fields',                 'ple_ec3_fields',                 0, 1 );
add_filter( 'ple_args',                   'ple_ec3_args',                   1, 1 );
add_filter( 'ple_posts',                  'ple_ec3_posts',                  0, 2 );
add_filter( 'ple_placeholders',           'ple_ec3_placeholders',           1, 3 );
add_filter( 'ple_placeholdervalue',       'ple_ec3_placeholdervalue',       1, 5 );
add_filter( 'ple_placeholderdescription', 'ple_ec3_placeholderdescription', 1, 3 );

//-----------------------------------------------------------------------------

?>