    
<?php 

        global $wpdb;

        if (isset($_POST['user_note_reply_text']))
        {
            $note_reply_ids = $_POST['note_reply_ids'];
            $user_note_reply_text = $_POST['user_note_reply_text'];
            $insert_time = date("Y-m-d H:i:s");

            $table_name = $wpdb->prefix . 'super_sticky_notes';
            $prent_note = $wpdb->get_row("SELECT * FROM $table_name WHERE `id` = $note_reply_ids ", OBJECT);
            $prent_notes = json_decode(json_encode($prent_note), true);

            $user_id = $prent_notes['user_id'];
            $page_id = $prent_notes['page_id'];
            $parent_class = $prent_notes['parent_class'];
            $current_Class = $prent_notes['current_Class'];
            $note_position = $prent_notes['note_position'];
            $title = $prent_notes['title'];
            $next_conv_allowed = 0;
           
            
            $all_parent_id = $wpdb->get_row("SELECT `id` FROM $table_name WHERE `user_id` = $user_id AND `page_id` = $page_id AND `parent_class` = '".$parent_class."' AND `note_position` = $note_position AND `parent_id` = $note_reply_ids ", OBJECT);
            $all_parent_ids = json_decode(json_encode($all_parent_id), true);
            $all_id = $all_parent_ids['id'];

            if ($all_id) {

                $wpdb->update( $table_name,
                array(
                     'note_values' => $user_note_reply_text,
                     'next_conv_allowed' => $next_conv_allowed
                    ),
                array(
                    'id'=> $all_id
                    ),
                array('%s', '%d'),
                array('%d')
                );

            }
            else{


                $insert = $wpdb->insert( $table_name, 
                array(
                    'user_id' => $user_id,
                    'page_id' => $page_id,
                    'parent_class' => $parent_class,
                    'current_Class' => $current_Class,
                    'note_position' => $note_position,
                    'note_values' => $user_note_reply_text,
                    'title' => $title,
                    'next_conv_allowed' => $next_conv_allowed,
                    'parent_id' => $note_reply_ids
                ),
                array('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%d', '%d')
                );


            }
            
        }
    ?>
    <div class="super-sticky-notes">
        <div class="sticky-setting-title"><div class=setting-icon><h1><?php _e('User Question Lists', 'wp_super_sticky_notes'); ?></h1></div></div>
        <div class="sticky-top-bar">
            <div class="tab">
                <button class="tablinks active" onclick="openTab(event, 'all')"><?php _e('All', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                <button class="tablinks" onclick="openTab(event, 'approved')"><?php _e('Approved', 'wp_super_sticky_notes'); ?></button><div class="tab-icons"></div>
                <button class="tablinks" onclick="openTab(event, 'disapproved')"><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></button>
            </div>
            <div class="tab-search">
                <form method="POST">
                    <input type="text" name="search_value" placeholder="Search here..." required>
                    <button class="tab-search-button" type="submit">Search</button>
                </form>
            </div>
        </div>

        <div id="all" class="tabcontent" style="display:block;" >

            <table class="sticky-notes-data-table">
                <tr class="note-heading-wrapper">                
                    <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Status', 'wp_super_sticky_notes'); ?></th>
                </tr>
                <?php
                    global $wpdb;
                    $current_user_id = get_current_user_id();

                    if (isset($_POST['search_value']))
                    {   

                        $search_value = $_POST['search_value'];
                        $table_name = $wpdb->prefix . 'super_sticky_notes';
                        $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name 
                        WHERE `user_id` = $current_user_id AND `note_values` LIKE '%".$search_value."%' ", OBJECT);
                        $all_valus_notes = json_decode(json_encode($all_valus_notes), true);

                    }else{

                    $table_name = $wpdb->prefix . 'super_sticky_notes';
                    $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name WHERE `user_id` = $current_user_id ", OBJECT);                   
                    $all_valus_notes = json_decode(json_encode($all_valus_notes), true);
                    }
                    
                    foreach ($all_valus_notes as $note_values){
                        $approve_msg = ($note_values['priv']) ? __('Private Commment', 'wp_super_sticky_notes') : __('Not Approved', 'wp_super_sticky_notes');

                ?>
                <tr>
                    
                    <td><?php echo $note_values['note_values']; ?></td>
                    <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                    <td><?php echo $note_values['insert_time']; ?></td>
                    <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Not approved by admin', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_reply'] == ''){ _e('Not reply by admin', 'wp_super_sticky_notes'); }else{ echo $note_values['note_reply']; } ?></td>
                    <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Nil', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_repliedOn'] == ''){ _e('No date', 'wp_super_sticky_notes'); }else{ echo $note_values['note_repliedOn']; } ?></td>
                    <td>
                        <?php if($note_values['note_status'] == 'Approved'){ ?>
                           <div class="approved"><?php _e('Approved', 'wp_super_sticky_notes'); ?></div>
                        <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                            <div class="disapproved"><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></div>
                        <?php }else{?> 
                            <div class="disapproved om">
                                <?php echo $approve_msg; ?>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
                <?php
                    }
                ?>
            </table>

        </div>
        
        <div id="approved" class="tabcontent">

            <table class="sticky-notes-data-table">
                <tr class="note-heading-wrapper">
                    <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Status', 'wp_super_sticky_notes'); ?></th>
                </tr>
                <?php
                    global $wpdb;
                    $current_user_id = get_current_user_id();

                    if (isset($_POST['search_value']))
                    {   

                        $search_value = $_POST['search_value'];
                        $table_name = $wpdb->prefix . 'super_sticky_notes';
                        $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name 
                        WHERE `user_id` = $current_user_id AND `note_status` = 'Approved' AND `note_values` LIKE '%".$search_value."%' ", OBJECT);
                        $all_valus_notes = json_decode(json_encode($all_valus_notes), true);

                    }else{

                    $table_name = $wpdb->prefix . 'super_sticky_notes';
                    $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name WHERE `user_id` = $current_user_id AND `note_status` = 'Approved' ", OBJECT);                   
                    $all_valus_notes = json_decode(json_encode($all_valus_notes), true);
                    }

                    foreach ($all_valus_notes as $note_values){
                ?>
                <tr>
                    
                    <td><?php echo $note_values['note_values']; ?></td>
                    <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                    <td><?php echo $note_values['insert_time']; ?></td>
                    <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Not approved by admin', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_reply'] == ''){ _e('Not reply by admin', 'wp_super_sticky_notes'); }else{ echo $note_values['note_reply']; } ?></td>
                    <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Nil', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_repliedOn'] == ''){ _e('No date', 'wp_super_sticky_notes'); }else{ echo $note_values['note_repliedOn']; } ?></td>
                    <td>
                        <?php if($note_values['note_status'] == 'Approved'){ ?>
                           <div class="approved"><?php _e('Approved', 'wp_super_sticky_notes'); ?></div>
                        <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                            <div class="disapproved"><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></div>
                        <?php }?> 
                    </td>
                </tr>
                <?php
                    }
                ?>
            </table>

        </div>
        <div id="disapproved" class="tabcontent">

            <table class="sticky-notes-data-table">
                <tr class="note-heading-wrapper">
                    <th><?php _e('Asked Question', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Page/Post', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('AskedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Reply', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('RepliedOn', 'wp_super_sticky_notes'); ?></th>
                    <th><?php _e('Status', 'wp_super_sticky_notes'); ?></th>
                </tr>
                <?php
                    global $wpdb;
                    $current_user_id = get_current_user_id();

                    if (isset($_POST['search_value']))
                    {   

                        $search_value = $_POST['search_value'];
                        $table_name = $wpdb->prefix . 'super_sticky_notes';
                        $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name 
                        WHERE `user_id` = $current_user_id AND `note_status` = 'Approved' AND `note_values` LIKE '%".$search_value."%' ", OBJECT);
                        $all_valus_notes = json_decode(json_encode($all_valus_notes), true);

                    }else{

                    $table_name = $wpdb->prefix . 'super_sticky_notes';
                    $all_valus_notes = $wpdb->get_results("SELECT * FROM $table_name WHERE `user_id` = $current_user_id AND `note_status` = 'Disapproved' ", OBJECT);                   
                    $all_valus_notes = json_decode(json_encode($all_valus_notes), true);
                    }

                    foreach ($all_valus_notes as $note_values){
                ?>
                <tr>
                    
                    <td><?php echo $note_values['note_values']; ?></td>
                    <td class="note-title"><a href="<?php echo get_permalink($note_values['page_id']); ?>" target="_blank"><?php echo $note_values['title']; ?></a></td>
                    <td><?php echo $note_values['insert_time']; ?></td>
                    <td class="note-class-view"><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Not approved by admin', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_reply'] == ''){ _e('Not reply by admin', 'wp_super_sticky_notes'); }else{ echo $note_values['note_reply']; } ?></td>
                    <td><?php if($note_values['note_status'] == 'Disapproved'){ ?> <div class="note-disapproved"><?php _e('Nil', 'wp_super_sticky_notes'); ?></div> <?php }elseif( $note_values['note_repliedOn'] == ''){ _e('No date', 'wp_super_sticky_notes'); }else{ echo $note_values['note_repliedOn']; } ?></td>
                    <td>
                        <?php if($note_values['note_status'] == 'Approved'){ ?>
                           <div class="approved"><?php _e('Approved', 'wp_super_sticky_notes'); ?></div>
                        <?php }elseif($note_values['note_status'] == 'Disapproved'){ ?> 
                            <div class="disapproved"><?php _e('Disapproved', 'wp_super_sticky_notes'); ?></div>
                        <?php }?> 
                    </td>
                </tr>
                <?php
                    }
                ?>
            </table>

        </div>
    </div>