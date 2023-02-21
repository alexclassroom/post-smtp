<?php

if( !class_exists( 'PostmanEmailQueryLog' ) ):
class PostmanEmailQueryLog {

    private $db = '';
    private $table = 'post_smtp_logs';
    private $query = ''; 
    private $columns = array();


    /**
     * The Construct PostmanEmailQueryLog
     * 
      * @since 2.5.0
     * @version 1.0.0
     */
    public function __construct() {

        global $wpdb;
        $this->db = $wpdb;
        $this->table = $this->db->prefix . $this->table;

        
    }


    /**
     * Get Logs
     * 
     * @param $args String
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_logs( $args = array() ) {

        $clause_for_date = empty( $args['search'] ) ? $this->query .= " WHERE" : $this->query .= " AND";

        $args['search_by'] = array(
            'original_subject',
            'success',
            'solution',
            'to_header'
        );

        if( !isset( $args['columns'] ) ) {

            $this->columns = array(
                'id',
                'original_subject',
                'original_to',
                'success',
                'solution',
                'time'
            );

        }
        else {

            $this->columns = $args['columns'];

        }

        $this->columns = implode( ',', $this->columns );

        $this->query = "SELECT {$this->columns} FROM `{$this->table}`";

        //Search
        if( !empty( $args['search'] ) ) {

            $this->query .= " WHERE";
            $counter = 1;

            foreach( $args['search_by'] as $key ) {
                
                $this->query .= " {$key} LIKE '%{$args["search"]}%'";
                $this->query .= $counter != count( $args['search_by'] ) ? ' OR' : '';
                $counter++;

            }

        }

        //Date Filter :)
        if( isset( $args['from'] ) ) {
                
            $this->query .= " {$clause_for_date} time >= {$args['from']}";

        }

        if( isset( $args['to'] ) ) {

            $clause_for_date = ( empty( $args['search'] ) && !isset( $args['from'] ) ) ? " WHERE" : " AND";

            $this->query .= " {$clause_for_date} time <= {$args['to']}";

        }

        //Order By
        if( !empty( $args['order'] ) && !empty( $args['order_by'] ) ) {

            $this->query .= " ORDER BY {$args['order_by']} {$args['order']}";

        }

        //Lets say from 0 to 25
        if( isset( $args['start'] ) && isset( $args['end'] ) ) {
            
            $this->query .= " LIMIT {$args['start']}, {$args['end']}";

        }

        return $this->db->get_results( $this->query );

    }


    /**
     * Get Filtered Rows Count
     * Total records, after filtering (i.e. the total number of records after filtering has been applied - not just the number of records being returned for this page of data).
     * 
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_filtered_rows_count() {

        $query = str_replace( $this->columns, 'COUNT(*) as count', $this->query );

        //Remove LIMIT clouse to use COUNT clouse properly 
        $query = substr( $query, 0, strpos( $query, "LIMIT" ) );

        return $this->db->get_results( $query );

    }


    /**
     * Gets Total Rows Count
     * Total records, before filtering (i.e. the total number of records in the database)
     * 
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_total_row_count() {

        return $this->db->get_results(
            "SELECT COUNT(*) as count FROM `{$this->table}`;"
        );

    }


    /**
     * Get Last Log ID
     * 
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_last_log_id() {

        return $this->db->get_results(
            "SELECT id FROM `{$this->table}` ORDER BY id DESC LIMIT 1;"
        );

    }


    /**
     * Delete Logs
     * 
     * @param $ids Array
     * @since 2.5.0
     * @version 1.0.0
     */
    public function delete_logs( $ids = array() ) {
        
        $ids = implode( ',', $ids );
        $ids = $ids == -1 ? '' : "WHERE id IN ({$ids});";

        return $this->db->query(
            "DELETE FROM `{$this->table}` {$ids}"
        );

    }


    /**
     * Get All Logs
     * 
     * @param $ids Array
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_all_logs( $ids = array() ) {

        $ids = implode( ',', $ids );
        $ids = $ids == -1 ? '' : "WHERE id IN ({$ids});";

        return $this->db->get_results(
            "SELECT * FROM `{$this->table}` {$ids}"
        );


    }


    /**
     * Get Log
     * 
     * @param $id Int
     * @param $columns Array
     * @since 2.5.0
     * @version 1.0.0
     */
    public function get_log( $id, $columns = array() ) {

        $columns = empty( $columns ) ? '*' : implode( ',', $columns );

        return $this->db->get_row(
            "SELECT {$columns} FROM `{$this->table}` WHERE id = {$id};",
            ARRAY_A
        );


    }

}
endif;