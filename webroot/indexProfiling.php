<?php

class IndexProfiling {

    static $URL = "localhost:8080/chat/FriendsList";
    static $COOKIE = "app=hash";

    public function profile($times = 1000) {
        $initTime = microtime(true);
        for ($i = 0; $i < $times; $i++) {
            $this->makeRequest();
        }
        $endTime = microtime(true);

        return $endTime - $initTime;
    }

    public function makeRequest() {
        $handle = curl_init(self::$URL);
        curl_setopt($handle, CURLOPT_COOKIE, self::$COOKIE);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, 1);
        curl_exec($handle);
        curl_close($handle);
    }

}



$p = new IndexProfiling();
echo $p->profile() . " secs\n"; // 1.24s
