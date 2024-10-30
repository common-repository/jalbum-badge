<?php
/*
 * Plugin name: Jalbum Badge
 * Description: Adds a Jalbum blog badge widget to display your Jalbum photo albums in your sidebar.
 * Version: 1.0.3
 * Author: Kristoffer Jelbring
 * Author URI: http://jalbum.net/users/kristoffer/
 */

$badge = new JalbumBadge();
add_action("widgets_init", array($badge, "init"));
register_activation_hook( __FILE__, array($badge, "activate"));
register_deactivation_hook( __FILE__, array($badge, "deactivate"));

class JalbumBadge {

    var $id = "jalbum_badge";
    var $name = "Jalbum Badge";
    var $description = "Add a Jalbum Badge to your sidebar";
    var $default_options = array(
        "header" => "My Jalbums",
        "look" => 1,
        "width" => 180,
        "count" => 5
    );

    function init() {
        wp_register_sidebar_widget($this->id, $this->name, array($this, "widget"), array("description" => $this->description));
        wp_register_widget_control($this->id, $this->name, array($this, "widget_control"));
    }

    function activate() {
        if (!get_option($this->id)){
            add_option($this->id, $this->default_options);
        } else {
            update_option($this->id, $this->default_options);
        }
    }

    function deactivate() {
        delete_option($this->id);
    }

    function widget($args) {
        echo $args['before_widget'];

        $options = get_option($this->id);

        echo "<div style=\"clear:none;\">";
        if ($this->is_valid_text_option($options, "username")) {
            echo "<script type=\"text/javascript\" charset=\"utf-8\">".
                 $this->get_badge_settings($options).
                 "</script>".
                 "<script src=\"".$this->get_badge_script_source($options, htmlspecialchars($options["username"]))."\" type=\"text/javascript\" charset=\"utf-8\"></script>";
        } else {
            echo "<p>You need to enter your jalbum.net username to use the Jalbum Badge. When you do your badge will be displayed here.</p>";
        }
        echo "</div>";

        echo $args['after_widget'];
    }

    function get_badge_script_source($options, $username) {
        $source = "http://jalbum.net/badge/load.js?u=$username";
        if ($this->is_valid_numeric_options($options, "count")) {
            $source .= "&c=".$options["count"];
        }
        return $source."&v=1";
    }

    function get_badge_settings($options) {
        $settings = "";
        if ($this->is_valid_text_option($options, "header")) {
            $settings .= "_ja_badge_header = \"".htmlspecialchars($options["header"], ENT_QUOTES)."\";\n";
        }
        if ($this->is_valid_numeric_options($options, "width")) {
            $settings .= "_ja_badge_width = ".$options["width"].";\n";
        }
        if ($this->is_valid_numeric_options($options, "look")) {
            $settings .= "_ja_badge_look = ".$options["look"].";\n";
        }
        return $settings;
    }

    function is_valid_numeric_options($options, $option_name) {
        return isset($options[$option_name]) && is_numeric($options[$option_name]);
    }

    function is_valid_text_option($options, $option_name) {
        return isset($options[$option_name]) && !empty($options[$option_name]);
    }

    function widget_control() {
        $options = get_option($this->id);

        if (isset($_POST[$this->id."-username"])) {
            $options["username"] = strip_tags(stripslashes($_POST[$this->id."-username"]));
			$options["header"] = strip_tags(stripslashes($_POST[$this->id."-header"]));
            $options["width"] = strip_tags(stripslashes($_POST[$this->id."-width"]));
            $options["look"] = strip_tags(stripslashes($_POST[$this->id."-look"]));
            $options["count"] = strip_tags(stripslashes($_POST[$this->id."-count"]));
            update_option($this->id, $options);
        }

        echo "<p><label for=\"$this->id-username\">Your jalbum.net username</label><br />".
            "<input style=\"width: 200px;\" id=\"$this->id-username\" name=\"$this->id-username\" type=\"text\" value=\"".$options["username"]."\"/></p>";

        echo "<p><label for=\"$this->id-header\">Badge header</label><br />".
            "<input style=\"width: 200px;\" id=\"$this->id-header\" name=\"$this->id-header\" type=\"text\" value=\"".$options["header"]."\"/></p>";

        echo "<p><label for=\"$this->id-width\">Badge width</label><br />".
            "<input style=\"width: 200px;\" id=\"$this->id-width\" name=\"$this->id-width\" type=\"text\" value=\"".$options["width"]."\"/>px</p>";

        echo "<p><label>Badge look</label><br />".
            $this->look_option(1, "Light", $options["look"]).
            $this->look_option(2, "Dark", $options["look"]).
            $this->look_option(3, "Blue", $options["look"])."<br />".
            $this->look_option(4, "Pink", $options["look"]).
            $this->look_option(5, "Green", $options["look"]).
            $this->look_option(6, "Brown", $options["look"]).
            "</p>";

        echo "<p><label for=\"$this->id-count\">Badge max nr. of albums</label><br />".
            "<input style=\"width: 200px;\" id=\"$this->id-count\" name=\"$this->id-count\" type=\"text\" value=\"".$options["count"]."\"/></p>";
    }

    function look_option_image($name) {
        $filename = strtolower($name).".png";
        if (file_exists(dirname(__FILE__)."includes/".$filename)) {
            $path = str_replace(ABSPATH, get_settings("siteurl")."/", dirname(__FILE__));
        } else {
            $path = "../wp-content/plugins/jalbum-badge/";
        }
        return $path."includes/".$filename;
    }

    function look_option($value, $name, $current_value) {
        $checked = $current_value == $value ? " checked=\"checked\"" : "";
        return "<input type=\"radio\" name=\"$this->id-look\" value=\"$value\"$checked />".
        "<img src=\"".$this->look_option_image($name)."\" alt=\"$name\" title=\"$name\" style=\"vertical-align:middle;margin:4px 4px 12px 6px;\" />";
    }
}

?>