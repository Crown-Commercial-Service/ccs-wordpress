<?php
defined('ABSPATH') or die('No direct script access allowed.');

if (!class_exists('Editorial_Metadata_Input_Date_Handler')) {
    require_once 'editorial-metadata-input-handler.php';

    class Editorial_Metadata_Input_Date_Handler extends Editorial_Metadata_Input_Handler
    {
        /**
         * Class constructor that defines input type.
         *
         * @since   1.20.0
         */
        public function __construct()
        {
            $this->type = 'date';
        }

        /**
         * Render input html.
         *
         * @access  protected
         * @since   1.20.0
         *
         * @param   array   $inputOptions   Input options
         * @param   mixed   $value          Actual input value
         */
        protected function renderInput($inputOptions = array(), $value = null)
        {
            $input_name = isset($inputOptions['name']) ? $inputOptions['name'] : '';
            $input_label = isset($inputOptions['label']) ? $inputOptions['label'] : '';
            $input_description = isset($inputOptions['description']) ? $inputOptions['description'] : '';

            $value_formatted = !empty($value) ? self::show_date_or_datetime(intval($value)) : '';

            self::renderLabel($input_label, $input_name);

            if (mb_strlen($input_description) > 0) {
                self::renderDescription($input_description, $input_name);
            }

            printf(
                '<input
                    type="text"
                    id="%s"
                    name="%1$s"
                    value="%2$s"
                    class="date-time-pick"
                    data-alt-field="%1$s_hidden"
                    data-alt-format="%3$s"
                />',
                $input_name,
                $value_formatted,
                pp_convert_date_format_to_jqueryui_datepicker('Y-m-d')
            );

            $field_value = empty($value) ? '' : date('Y-m-d H:i', $value);

            printf(
                '<input
                    type="hidden"
                    name="%s_hidden"
                    value="%s"
                />',
                $input_name,
                $field_value
            );
        }

        /**
         * Show date or datetime.
         *
         * @since   1.20.0
         *
         * @param   int     $current_date
         *
         * @return  string
         */
        private static function show_date_or_datetime($current_date)
        {
            $date_format = get_option('date_format');

            if (date('Hi', $current_date) == '0000') {
                return date_i18n($date_format, $current_date);
            }

            return date_i18n("{$date_format} H:i", $current_date);
        }

        /**
         * Render input-preview html.
         *
         * @access  protected
         * @since   1.20.0
         *
         * @param   array   $inputOptions   Input options
         * @param   mixed   $value          Actual input value
         */
        protected function renderInputPreview($inputOptions = array(), $value = null)
        {
            $input_name = isset($inputOptions['name']) ? $inputOptions['name'] : '';
            $input_label = isset($inputOptions['label']) ? $inputOptions['label'] : '';
            $input_description = isset($inputOptions['description']) ? $inputOptions['description'] : '';
            $value = !empty($value) ? self::show_date_or_datetime(intval($value)) : $value;

            self::renderLabel(
                $input_label . self::generateDescriptionHtml($input_description),
                $input_name
            );

            if (!empty($value)) {
                printf(
                    '<span class="pp_editorial_metadata_value">%s</span>',
                    $value
                );
            } else {
                self::renderValuePlaceholder();
            }

            printf(
                '<input
                    type="hidden"
                    id="%s"
                    name="%1$s"
                    value="%2$s"
                />',
                $input_name,
                $value
            );
        }

        /**
         * Get meta-input value html formatted.
         *
         * @static
         * @since   1.20.0
         *
         * @param   mixed   $value  Actual input value
         *
         * @return  string
         */
        public static function getMetaValueHtml($value = null)
        {
            if (empty($value)) {
                return '';
            }

            // All day vs. day and time
            $date = date(get_option('date_format'), $value);
            $time = date(get_option('time_format'), $value);

            $output = date('Hi', $value) === '0000'
                ? $date
                : sprintf(__('%1$s at %2$s', 'publishpress'), $date, $time);

            return esc_html($output);
        }
    }
}
