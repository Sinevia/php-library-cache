<?php

namespace Sinevia;

class Cache {

    const TABLE_CACHE = 'snv_caches_cache';

    public static $tableCacheSchema = array(
        array("Id", "STRING", "NOT NULL PRIMARY KEY"),
        array("Key", "STRING"),
        array("Value", "TEXT"),
        array("ExpiresAt", "STRING", " DEFAULT NULL"),
        array("CreatedAt", "STRING", " DEFAULT NULL"),
        array("UpdatedAt", "STRING", " DEFAULT NULL")
    );

    public static function tableCreate() {
        if (self::tableCache()->exists() == false) {
            return self::tableCache()->create(self::$tableCacheSchema);
        }
        return true;
    }
    
    public static function tableDelete() {
        if (self::tableCache()->exists() == true) {
            return self::tableCache()->delete();
        }
        return true;
    }

    public static function tableCache() {
        return db()->table(self::TABLE_CACHE);
    }

    public static function findCacheByKey($key) {
        return self::tableCache()->where('Key', '=', $key)->selectOne();
    }

    public static function findCacheById($key) {
        return self::tableCache()->where('Id', '=', $key)->selectOne();
    }

    public static function createOrUpdateCache($key, $value, $expiresAt = '+1 day') {
        if (self::tableCache()->where('Key', '=', $key)->numRows() < 1) {
            $result = self::createCache($key, $value, $expiresAt);
            return is_null($result) ? false : true;
        } else {
            return self::updateCache($key, $value, $expiresAt);
        }
    }

    public static function createCache($key, $value, $expiresAt = '+1 day') {
        $data = [
            'Id' => \Sinevia\Uid::microUid(),
            'Key' => $key,
            'Value' => json_encode($value),
            'CreatedAt' => date('Y-m-d H:i:s'),
            'UpdatedAt' => date('Y-m-d H:i:s'),
            'ExpiresAt' => date('Y-m-d H:i:s', strtotime($expiresAt)),
        ];

        $isSuccess = self::tableCache()->insert($data);

        if ($isSuccess == false) {
            return null;
        }

        return $data['Id'];
    }

    public static function updateCache($key, $value, $expiresAt = '+1 day') {
        $data = [
            'Value' => json_encode($value),
            'UpdatedAt' => date('Y-m-d H:i:s'),
            'ExpiresAt' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        ];
        $isSuccessful = \App\Plugins\Cache::tableCache()
                ->where('Key', '=', $key)
                ->update($data);
        return $isSuccessful;
    }

}
