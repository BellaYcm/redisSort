<?php

    class RedisSort {

        public function getTopTeamByAll ()
        {
            if ( ! $this->redis->get ( WEBNAME . "TopTeamByAll" ) )
            {
                $key = $this->redis->keys ( WEBNAME . 'sort*' );
                foreach ( $key as $k => $v )
                {
                    $this->redis->delete ( $v );
                }
                $signUpInfo = Sign_up_info::all ()->toArray ();
                foreach ( $signUpInfo as $k => $v )
                {
                    $runInfo = Run_info::where ( "group_id" , $v[ 'group_id' ] );
                    $runInfoSum = $runInfo->get ()->sum ( "run_data" );
                    $runInfoGroup = current ( $runInfo->take ( 1 )->get ()->toArray () );
                    $this->redis->LPUSH ( WEBNAME . "sort_id" , $k );
                    $this->redis->SET ( WEBNAME . "sort_name_" . $k , $runInfoGroup[ 'group_name' ] );
                    $this->redis->SET ( WEBNAME . "sort_count_" . $k , $runInfoSum );
                    $this->redis->SET ( WEBNAME . "sort_group_id" . $k , $runInfoGroup[ 'group_id' ] );
                    $this->redis->SET ( WEBNAME . "sort_head_pic" . $k , $runInfoGroup[ 'head_pic' ] );
                }

                $groupNameSort = $this->redis->SORT ( WEBNAME . "sort_id" , [ 'BY' => WEBNAME . 'sort_count_*' , 'LIMIT' => [ 0 , 20 ] , 'SORT' => 'DESC' , 'GET' => WEBNAME . 'sort_name_*' ] );
                $sumSort = $this->redis->SORT ( WEBNAME . "sort_id" , [ 'BY' => WEBNAME . 'sort_count_*' , 'LIMIT' => [ 0 , 20 ] , 'SORT' => 'DESC' , 'GET' => WEBNAME . 'sort_count_*' ] );
                $groupIdSort = $this->redis->SORT ( WEBNAME . "sort_id" , [ 'BY' => WEBNAME . 'sort_count_*' , 'LIMIT' => [ 0 , 20 ] , 'SORT' => 'DESC' , 'GET' => WEBNAME . 'sort_group_id*' ] );
                $headPicSort = $this->redis->SORT ( WEBNAME . "sort_id" , [ 'BY' => WEBNAME . 'sort_count_*' , 'LIMIT' => [ 0 , 20 ] , 'SORT' => 'DESC' , 'GET' => WEBNAME . 'sort_head_pic*' ] );
                $sort = [ ];
                $count = count ( $sumSort );
                for ( $i = 0 ; $i < $count ; $i ++ )
                {
                    $sort[ $i ][ 'gname' ] = $groupNameSort[ $i ];
                    $sort[ $i ][ 'sum' ] = $sumSort[ $i ];
                    $sort[ $i ][ 'gid' ] = $groupIdSort[ $i ];
                    $sort[ $i ][ 'headPic' ] = $headPicSort[ $i ];
                }
                $this->redis->setex ( WEBNAME . "TopTeamByAll" , 1200 , json_encode ( $sort ) );
            }

            return json_decode ( $this->redis->get ( WEBNAME . "TopTeamByAll" ) );
        }

    }

?>

