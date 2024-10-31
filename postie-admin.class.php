<?php

class PostieAdmin {

    /**
     * Takes a value and builds a simple simple yes/no select box
     */
    static function boolean_select_html($label, $id, $current_value, $recommendation = NULL, $options = null) {

        $html = "<tr>
	<th scope='row'><label for='$id'>$label</label>";

        if (!(is_array($options) && count($options) == 2)) {
            $options = Array(__('Yes', 'postie'), __('No', 'postie'));
        }

        $html .= "</th>
	<td><select name='$id' id='$id'>
            <option value='1'>" . $options[0] . "</option>
            <option value='0' " . (!$current_value ? "selected='selected'" : "") . ">" . $options[1] . '</option>
        </select>';
        if (!empty($recommendation)) {
            $html .= '<p class = "description">' . $recommendation . '</p>';
        }
        $html .= "</td>\n</tr>";

        return $html;
    }

    /**
     * This takes an array and display a text box for editing
     */
    static function textarea_html($label, $id, $current_value, $recommendation = NULL) {
        $html = "<tr><th scope='row'><label for='$id'>$label</label>";

        $html .= "</th>";

        $html .= "<td><br /><textarea cols=40 rows=3 name='$id' id='$id'>";
        $current_value = preg_split("/[\r\n]+/", esc_attr(trim($current_value)));
        if (is_array($current_value)) {
            foreach ($current_value as $item) {
                $html .= "$item\n";
            }
        }
        $html .= "</textarea>";
        if ($recommendation) {
            $html .= "<p class='description'>" . $recommendation . "</p>";
        }
        $html .= "</td></tr>";
        return $html;
    }

    /**
     * Takes a value and builds a simple simple yes/no select box
     */
    static function select_html($label, $id, $current_value, $options, $recommendation = NULL) {

        $html = "<tr>
	<th scope='row'><label for='$id'>$label</label>";

        $html .= "</th><td><select name='$id' id='$id'>";
        foreach ($options as $value) {
            $html .= "<option value='$value' " . ($value == $current_value ? "selected='selected'" : "") . ">" . __($value, 'postie') . '</option>';
        }
        $html .= '</select>';
        if (!empty($recommendation)) {
            $html .= '<p class = "description">' . $recommendation . '</p>';
        }
        $html .= "</td>\n</tr>";

        return $html;
    }

    static function select_html2($label, $id, $current_value, $options, $recommendation = NULL) {
        DebugDump($options);
        $html = "<tr>
	<th scope='row'><label for='$id'>$label</label></th>\n";

        $html .= "<td><select name='$id' id='$id'>\n";
        foreach ($options as $key => $value) {
            $html .= "<option value='$key' " . ($key == $current_value ? "selected='selected'" : "") . ">" . __($value, 'postie') . "</option>\n";
        }
        $html .= '</select>';
        if (!empty($recommendation)) {
            $html .= '<p class = "description">' . $recommendation . '</p>';
        }
        $html .= "</td>\n</tr>\n";

        return $html;
    }

}
