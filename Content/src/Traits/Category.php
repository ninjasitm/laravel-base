<?php

namespace Nitm\Content\Traits;

trait Category
{
    /**
     * Get existing categories from either slugs, is or md5'd ids.
     *
     * @method getExistingFrom
     *
     * @param [type] $group [description]
     *
     * @return {[type] [description]
     */
    public static function getExistingFrom($group, $titleAttribute = 'title')
    {
        return static::filterByType($group, $titleAttribute)->select('id')->get();
    }
        /**
         * Get single existing category from either slugs, is or md5'd ids.
         *
         * @method getExistingFrom
         *
         * @param [type] $group [description]
         *
         * @return {[type] [description]
         */
        public static function getSingleExistingFrom($data, $titleAttribute = 'title')
        {
            if (is_numeric($data) || $data instanceof \Model && $data->exists) {
                return $data;
            }
            $existing = static::filterByType($data, $titleAttribute)->select('id')->first();

            $existing = $existing ? $existing->id : null;
            return $existing;
        }

    /**
     * Filter categories by the type.
     *
     * @param [type] $query [description]
     * @param [type] $types [description]
     *
     * @return [type] [description]
     */
    public function scopeFilterByType($query, $types, $titleAttribute = 'title')
    {
        $query->where(function ($query) use ($types, $titleAttribute) {
            $types = (array) $types;
            $ids = array_filter($types, function ($type) {
                return is_numeric($type);
            });
            $ids = array_merge($ids, array_filter($types, function ($type) {
                if (is_array($type) && isset($type['id'])) {
                    $type = $category['id'];
                }
                return is_numeric($type);
            }));
            $md5Ids = array_filter($types, function ($type) use ($types) {
                $parts = is_array($type) ? $type : explode('-', $type);
                return $this->isValidMd5(array_pop($parts));
            });
            $strings = array_filter($types, function ($type) {
                return is_string($type) && !is_numeric($type);
            });

            if (count($strings)) {
                foreach ($strings as $string) {
                    $query->orWhere($titleAttribute, 'like', '%'.$string.'%');
                }
                $query->orWhere(function ($query) use ($strings) {
                    $query->whereIn('slug', array_map(function ($string) {
                        return static::getSlugFromMd5Id($string);
                    }, $strings));
                });
            }

            if (count($ids)) {
                $query->orWhere(function ($query) use ($ids) {
                    $query->whereIn('id', $ids);
                });
            }

            if (count($md5Ids)) {
                $query->orWhere(function ($query) use ($md5Ids) {
                    $query->whereIn(\DB::raw('MD5(id::text)'), array_map(function ($id) {
                        $parts = explode('-', $id);
                        return array_pop($parts);
                    }, $md5Ids));
                });
            }
        });
    }
}
