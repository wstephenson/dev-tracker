<?php
class Post extends Kurl {
    public $topic;
    public $uid;
    public $text;
    public $url;
    public $source;

    public function setTimestamp( $timestamp ){
        $this->timestamp = $timestamp;
    }

    public function setTopic( $title, $url ){
        $this->topic = array(
            'title' => trim( $title ),
            'url' => trim( $url )
        );
    }

    public function setText( $text ){
        $this->text = trim( $text );
    }

    public function setUrl( $url ){
        $this->url = trim( $url );
    }

    public function setUserId( $uid ){
        $this->uid = $uid;
    }

    public function setSource( $source ){
        $this->source = $source;
    }

    public function setSection( $section ){
        $this->section = $section;
    }

    public function isValid( $filterData ){
        if( strlen( $this->text ) <= 0 ) :
            return false;
        endif;

        // Filter for specific forums if we want
        if( isset( $filterData[ 'matchOnly' ] ) ):
            $invalid = true;

            if( !is_array( $filterData[ 'matchOnly' ] ) ):
                $filterData[ 'matchOnly' ] = array( $filterData[ 'matchOnly' ] );
            endif;

            for( $i = 0; $i < count( $filterData[ 'matchOnly' ] ); $i = $i + 1 ):
                if( $filterData[ 'matchOnly' ][ $i ] == $this->section ):
                    $invalid = false;
                    break;
                endif;
            endfor;

            if( $invalid ):
                return false;
            endif;
        endif;

        // Filter for specific forums if we want
        if( isset( $filterData[ 'exclude' ] ) ):
            $invalid = false;

            if( !is_array( $filterData[ 'exclude' ] ) ):
                $filterData[ 'exclude' ] = array( $filterData[ 'exclude' ] );
            endif;

            for( $i = 0; $i < count( $filterData[ 'exclude' ] ); $i = $i + 1 ):
                if( $filterData[ 'exclude' ][ $i ] == $this->section ):
                    $invalid = true;
                    break;
                endif;
            endfor;

            if( $invalid ):
                return false;
            endif;
        endif;

        return true;
    }

    private function postExists(){
        global $database;

        $query = 'SELECT COUNT(*) FROM posts WHERE url = :url LIMIT 1';
        $PDO = $database->prepare( $query );

        $PDO->bindValue( ':url', $this->url );

        $PDO->execute();

        if( $PDO->fetchColumn() > 0 ) :
            return true;
        endif;

        return false;
    }

    public function save( $configData = false ){
        if( !$this->isValid( $configData ) ):
            return false;
        endif;

        if( $this->postExists() ) :
            return false;
        endif;

        if( $this->timestamp <= 0 ) :
            $this->timestamp = time();
        endif;

        global $database;

        $query = 'INSERT INTO posts ( topic, topic_url, uid, url, source, content, timestamp ) VALUES( :topic, :topicUrl, :uid, :url, :source, :content, :timestamp )';
        $PDO = $database->prepare( $query );

        $PDO->bindValue( ':topic', $this->topic[ 'title' ] );
        $PDO->bindValue( ':topicUrl', $this->topic[ 'url' ] );
        $PDO->bindValue( ':uid', $this->uid );
        $PDO->bindValue( ':url', $this->url );
        $PDO->bindValue( ':source', $this->source );
        $PDO->bindValue( ':content', $this->text );
        $PDO->bindValue( ':timestamp', $this->timestamp );

        $result = $PDO->execute();

        if( $result ):
            return $result;
        endif;

        print_r( $PDO->errorInfo() );

        return false;
    }
}
