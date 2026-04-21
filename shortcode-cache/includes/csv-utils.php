<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function shortcode_cache_export_config_to_csv() {
    $config = get_option( 'shortcode_cache_config', array() );

    if ( ! is_array( $config ) ) {
        return '';
    }

    $csv_lines = array();
    $csv_lines[] = 'Shortcode Name,Id,Cache By Role,Cache By Page,Note';

    foreach ( $config as $item ) {
        if ( ! isset( $item['name'] ) ) {
            continue;
        }

        $name = isset( $item['name'] ) ? $item['name'] : '';
        $id = isset( $item['id'] ) ? $item['id'] : '';
        $cache_by_role = isset( $item['cache_by_role'] ) && $item['cache_by_role'] ? 'Yes' : 'No';
        $cache_by_page = isset( $item['cache_by_page'] ) && $item['cache_by_page'] ? 'Yes' : 'No';
        $note = isset( $item['note'] ) ? $item['note'] : '';

        $name = str_replace( '"', '""', $name );
        $id = str_replace( '"', '""', $id );
        $note = str_replace( '"', '""', $note );

        $csv_lines[] = '"' . $name . '","' . $id . '","' . $cache_by_role . '","' . $cache_by_page . '","' . $note . '"';
    }

    return implode( "\n", $csv_lines );
}

function shortcode_cache_import_config_from_csv( $csv_content ) {
    $lines = array_filter( array_map( 'trim', explode( "\n", $csv_content ) ) );

    if ( empty( $lines ) ) {
        return array(
            'success' => false,
            'message' => __( 'CSV file is empty', 'shortcode-cache' ),
        );
    }

    array_shift( $lines );

    if ( empty( $lines ) ) {
        return array(
            'success' => false,
            'message' => __( 'CSV file contains only headers', 'shortcode-cache' ),
        );
    }

    $imported_config = array();
    $errors = array();
    $row_number = 2;

    foreach ( $lines as $line ) {
        $fields = shortcode_cache_parse_csv_line( $line );

        if ( count( $fields ) < 1 ) {
            $row_number++;
            continue;
        }

        $validation = shortcode_cache_validate_csv_row( $fields, $row_number );

        if ( ! $validation['valid'] ) {
            $errors[] = $validation['error'];
            $row_number++;
            continue;
        }

        $imported_config[] = $validation['data'];
        $row_number++;
    }

    if ( empty( $imported_config ) && ! empty( $errors ) ) {
        return array(
            'success' => false,
            'message' => sprintf(
                __( 'No valid rows found. Errors: %s', 'shortcode-cache' ),
                implode( ' | ', $errors )
            ),
        );
    }

    return array(
        'success' => true,
        'config' => $imported_config,
        'errors' => $errors,
        'message' => sprintf(
            __( 'Successfully imported %d shortcodes', 'shortcode-cache' ),
            count( $imported_config )
        ),
    );
}

function shortcode_cache_parse_csv_line( $line ) {
    $fields = array();
    $current_field = '';
    $in_quotes = false;
    $i = 0;

    while ( $i < strlen( $line ) ) {
        $char = $line[ $i ];

        if ( $char === '"' ) {
            if ( $in_quotes && isset( $line[ $i + 1 ] ) && $line[ $i + 1 ] === '"' ) {
                $current_field .= '"';
                $i += 2;
                continue;
            }

            $in_quotes = ! $in_quotes;
            $i++;
            continue;
        }

        if ( $char === ',' && ! $in_quotes ) {
            $fields[] = trim( $current_field );
            $current_field = '';
            $i++;
            continue;
        }

        $current_field .= $char;
        $i++;
    }

    $fields[] = trim( $current_field );

    return $fields;
}

function shortcode_cache_validate_csv_row( $fields, $row_number ) {
    if ( count( $fields ) < 1 ) {
        return array(
            'valid' => false,
            'error' => sprintf(
                __( 'Row %d: Missing shortcode name', 'shortcode-cache' ),
                $row_number
            ),
        );
    }

    $name = sanitize_text_field( $fields[0] );

    if ( empty( $name ) ) {
        return array(
            'valid' => false,
            'error' => sprintf(
                __( 'Row %d: Shortcode name cannot be empty', 'shortcode-cache' ),
                $row_number
            ),
        );
    }

    $id = isset( $fields[1] ) ? sanitize_text_field( $fields[1] ) : '';
    $cache_by_role = false;
    $cache_by_page = false;

    if ( isset( $fields[2] ) ) {
        $cache_by_role_raw = strtolower( trim( $fields[2] ) );
        $cache_by_role = in_array( $cache_by_role_raw, array( 'yes', 'true', '1' ), true );
    }

    if ( isset( $fields[3] ) ) {
        $cache_by_page_raw = strtolower( trim( $fields[3] ) );
        $cache_by_page = in_array( $cache_by_page_raw, array( 'yes', 'true', '1' ), true );
    }

    $note = isset( $fields[4] ) ? sanitize_textarea_field( $fields[4] ) : '';

    return array(
        'valid' => true,
        'data' => array(
            'name' => $name,
            'id' => $id,
            'cache_by_role' => $cache_by_role,
            'cache_by_page' => $cache_by_page,
            'note' => $note,
        ),
    );
}