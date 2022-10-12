<?php
/*---------------------------------------------------------
Plugin Name: WP Radio Playlist
Author: carlosramosweb
Author URI: https://criacaocriativa.com
Donate link: https://donate.criacaocriativa.com
Description: Esse plugin é uma versão BETA. Sistema de lista das mais tocadas no Radio Web. Manualmente via Shortcode [wp_radio_playlist]
Text Domain: wp-radio-playlist
Domain Path: /languages/
Version: 1.0.0
Requires at least: 3.5.0
Tested up to: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html 
------------------------------------------------------------*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WP_Radio_Playlist' ) ) {   

    class WP_Radio_Playlist {

        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'init_functions' ) );
        }
        //=>

        public function init_functions() {
            add_action( 'init', array( $this, 'wp_register_posttype' ) );
            add_action( 'save_post', array( $this, 'wp_save_meta_box' ) );
            add_action( 'add_meta_boxes', array( $this, 'wp_register_meta_boxes' ) ); 
            add_shortcode( 'wp_radio_playlist', array( $this, 'wp_get_radio_playlist' ) );  
        }
        //=>

        public function wp_register_posttype() {
            $args = array(
                'public'                => true,
                'label'                 => '10 Mais Tocadas',
                'public_queryable'      => true,
                'exclude_from_search'   => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'show_in_nav_menus'     => true,
                'show_in_admin_bar'     => true,
                'capability_type'       => 'post',
                'query_var'             => true,
                'menu_icon'             => 'dashicons-format-audio',
                'supports'              => array( 'title', 'thumbnail' ), 
                'rewrite'               => array(
                    'slug'          => 'radio-playlist',
                    'with_front'    => false
                ),
                // 'title', 'editor', 'comments', 'revisions', 'trackbacks', 'author', 'excerpt', 'page-attributes', 'thumbnail', 'custom-fields', and 'post-formats'
            );
            register_post_type( 'radio-playlist', $args );
        }
        //=>

        public function wp_register_meta_boxes() {
            add_meta_box( 
                'meta-box-id', 
                __( 'Configuração', 'wp-radio-playlist' ), 
                array( $this, 'wp_radio_playlist_display_callback' ),
                'radio-playlist',
                'advanced',
                'high'
            );
        }
        //=>

        public function wp_radio_playlist_display_callback( $post ) { 
            $artist_song = get_post_meta( get_the_ID(), '_artist_song', true );
            if ( empty( $artist_song ) ) {
                $artist_song = "";
            }
            $link_song = get_post_meta( get_the_ID(), '_link_song', true );
            if ( empty( $link_song ) ) {
                $link_song = "";
            }
            ?>
            <p class="form-field _artist_song_field ">
                <label for="_artist_song">Nome da Artista</label>
                <input type="text" class="wp_input_text" name="_artist_song" value="<?php echo $artist_song; ?>" placeholder="Artist">
            </p>
            <p class="form-field _link_song_field ">
                <label for="_link_song">Link da Música</label>
                <input type="text" class="wp_input_link" name="_link_song" value="<?php echo $link_song; ?>" placeholder="https://">
            </p>
            <br/>
            <?php
        }
        //=>

        public function wp_save_meta_box( $post_id ) {
            if ( isset( $_POST ) ) {
                if ( isset( $_POST['post_type'] ) && $_POST['post_type'] == "radio-playlist" ) {
                    if ( array_key_exists( '_artist_song', $_POST ) ) {
                        update_post_meta(
                            $post_id,
                            '_artist_song',
                            $_POST['_artist_song']
                        );
                    }
                    if ( array_key_exists( '_link_song', $_POST ) ) {
                        update_post_meta(
                            $post_id,
                            '_link_song',
                            $_POST['_link_song']
                        );
                    }
                }
            }
        }
        //=>

        public function wp_get_radio_playlist( $atts ) {
            global $post;
            $this->wp_get_script_radio_playlist();

            $args = array(
                'numberposts' => 10,
                'post_type'   => 'radio-playlist',
                'orderby'    => 'menu_order',
                'sort_order' => 'asc'
            );

            $playlist = get_posts( $args );

            if ( $playlist ) {
				//echo '<h2 class="widgettitle">Mais Tocadas</h2>';
                echo '<ul class="ul-playlist">';
                $i = 1;
                foreach ( $playlist as $post ) { 
                    setup_postdata( $post ); 
                    $the_title = get_the_title();
                    $artist_song = get_post_meta( get_the_ID(), '_artist_song', true );
                    if ( empty( $artist_song ) ) {
                        $artist_song = "N/A";
                    }
                    $link_song = get_post_meta( get_the_ID(), '_link_song', true );
                    $js_song   = "#";
                    if ( ! empty( $link_song ) ) {
                        $link_song = strstr( $link_song, 'watch?v=' );
                        $link_song = str_replace( "watch?v=", "", $link_song );
                        $js_song   = "'" . $link_song . "'";
                        $js_title  = "'" . $the_title . "'";
                    }

                    $thumbnail_song = get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ); 
                    $thumbnail = '<div style="display:block; width:80px; height: 80px; float:left; margin-right:15px; background:#CCC;"></div>';
                    if ( ! empty( $thumbnail_song ) ) {
                        $thumbnail = '<div style="display:block; width:80px; height: 80px; float:left; margin-right:15px;">';
                        $thumbnail .= '<img src="' . $thumbnail_song . '" alt="' . $the_title . '" style="width:80px;">';
                        $thumbnail .= '</div>';
                    }

                    echo '<li class="li-playlist">';
                    echo '<span class="number-playlist">' . $i . '</span>';
                    echo $thumbnail;
                    echo  '<div style="float:left;">';
                    echo '<span class="artist-playlist">' . $artist_song . '</span>';
                    echo '<strong class="title-playlist">' . $the_title . '</strong>';
                    echo '<a href="javascript:;" class="link-playlist" onClick="on_play(' . $js_song . ', ' . $js_title . ')" data-toggle="modal" data-target=".exampleModal" target="_blank">Ouça agora &#10148;</a>';
                    echo  '</div>';
                    echo '<div style="clear:both;"></div>';
                    echo '</li>';

                    $i++;
                }
                echo '</ul>';
                wp_reset_postdata();
            }
            ?>
            <script type="text/javascript">
                function on_play( watch, title ) {
                    jQuery( "#iframe-youtube" ).attr( "src", '' );
                    //var on_watch = 'https://www.youtube.com/embed/' + watch + '?autoplay=1';
					var on_watch = 'https://www.youtube.com/embed/' + watch;
                    jQuery( "#iframe-youtube" ).attr( "src", on_watch );
                    jQuery( ".modal-title" ).html( title );
                }
				function close_play() {
					jQuery( "#iframe-youtube" ).attr( "src", '' );
				}
            </script>
            <?php
            $this->wp_get_modal_radio_playlist();
        }
        //=>

        public function wp_get_modal_radio_playlist() { ?>
            <div class="modal fade exampleModal" id="exampleModal" tabindex="-1" role="dialog" aria-hidden="true" style="padding-right: 0;" onClick="close_play()">
              <div class="modal-dialog" role="document" style="max-width: 98%;">
                <div class="modal-content" style="background-color: #000;">
                  <div class="modal-header" style="border-bottom: 1px solid #212529;">
                    <h5 class="modal-title" style="color: aliceblue;">Nome da Música</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <iframe class="iframe-youtube" id="iframe-youtube" src="" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                  </div>
                </div>
              </div>
            </div>
            <?php
        }
        //=>

        public function wp_get_script_radio_playlist() { ?>
            <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

            <!----<script src="//code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>---->
            <!----<script src="//cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>---->
            <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

            <style type="text/css">
				#exampleModal{
					top: 60px;
				}
                .iframe-youtube {
                    width:100%; min-height:340px; height: 65vh;
                }
                .ul-playlist {
                    display: block; margin:0; padding:0; text-transform: uppercase;
                }
                .li-playlist {
                    display:block; margin:0; padding:10px; border: 1px solid #a9a8a6; margin-bottom:2px;
                }
				.li-playlist {
					display: block;
					margin: 0;
					margin-bottom: 0px;
					padding: 10px;
					border: 1px solid #c1c1c0;
					border-bottom-color: rgb(193, 193, 192);
					border-bottom-style: solid;
					border-bottom-width: 1px;
					margin-bottom: 4px;
					border-bottom: 2px solid #827b7b;
				}
                .number-playlist {
                    display:block; width:50px; float:left; font-size:50px; color:#aca6a6; font-weight:bold; margin:0; padding:0px; text-align: center;
                }

                .artist-playlist {
                    display:block; font-size:16px; margin:0; padding:0px; color:#000;
                }
                .title-playlist {
                    display:block; font-size:20px; margin:0; padding:0px; font-weight:bold; color:#000;
                }
                a.link-playlist {
                    display:block; color:#cdc9ca; font-size:14px; margin:0; padding:0px; text-transform: none; text-decoration: none;
                }				
				.artist-playlist {
					display: block;
					font-size: 12px;
					margin: 0;
					padding: 0px;
					color: #000;
				}
				.title-playlist {
					display: block;
					font-size: 13px;
					margin: 0;
					padding: 0px;
					font-weight: bold;
					color: #565454;
				}
				.number-playlist {
					display: block;
					width: 44px;
					float: left;
					font-size: 50px;
					color: #d5d4d4;
					font-weight: bold;
					margin: 0;
					padding: 28px 0px 0px 0px;
					text-align: center;
				}
            </style>
            <?php
        }
        //=>

    }
    //=>

    new WP_Radio_Playlist();
}