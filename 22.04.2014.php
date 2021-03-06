<?php
    /*
    * Save product-to-keyword links
    * @param: array $data - POST data, list of keywords
    * @param: integer $product_id - product related to keywords
    * @return: boolean $res - result of transaction
    **/
    public static function saveProductKeywords($data,$product_id,$merchant = null){
        if(!$merchant && Zend_Registry::isRegistered('merchant')){
            $merchant = Zend_Registry::get('merchant');
        }
        if (!$merchant || empty($product_id) || !is_array($data)){
            return false;
        }
 
        //update keywords, only add new, don't delete old
        //$keyword_list = Util_Keyword::synchronizeKeywords(!empty($data)?$data:array(),$merchant,false);
        $keyword_list = new [!!!]_KeywordList($merchant->id);
        $keyword_list->load(false);
        $existing_keyword_list_sorted = self::arrayKey($keyword_list->toArray(),'value');
 
        //get all existing product-keywords links
        $product_keyword_list = new [!!!]_Product_KeywordList($product_id);
        $product_keyword_list->load(false);
        $existing_product_keyword_list_sorted = self::arrayKey($product_keyword_list->toArray(),'keyword_id');
 
        //delete all existing links
        $product_keyword_list->clear();
 
 
        //restore old and add new links based on POST data
        foreach($data as $keyword_details){
 
            //get keyword id if such value is already in list
            $keyword_value = trim(strtolower($keyword_details));
            $existing_keyword = ($keyword_value && isset($existing_keyword_list_sorted[$keyword_value]))?$existing_keyword_list_sorted[$keyword_value]:null;
            $existing_keyword_id = ($existing_keyword  && !empty($existing_keyword['id']))?$existing_keyword['id']:null;
 
            $existing_product_keyword = ($existing_keyword_id && isset($existing_product_keyword_list_sorted[$existing_keyword_id]))?$existing_product_keyword_list_sorted[$existing_keyword_id]:null;
            $existing_product_keyword_id = ($existing_product_keyword && !empty($existing_product_keyword['id']))?$existing_product_keyword['id']:null;
            //try to restore link
            $product_keyword_list->undelete($existing_product_keyword_id);
            //add new if link doesn't exist
            if (!$existing_product_keyword_id || !$product_keyword_list->get($existing_product_keyword_id)){
                $new_product_keyword = new [!!!]_Product_Keyword();
                $new_product_keyword->setData(array(
                    'product_id'   => $product_id, // product_id value
                    'keyword_id'   => $existing_keyword_id, // keyword id value
                ));
                $product_keyword_list->add($new_product_keyword);
            }
        }
        //save list with all changes
        $product_keyword_list->save();
        return true;
    }
