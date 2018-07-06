<?php

//  Controller          : product
//  Author              : Felix
//  Project             : product

namespace App\Http\Controllers\Product;

//use Illuminate\Support\Facades\Route;
//use App\Http\Controllers\baseController;
//use Illuminate\Routing\Controller as BaseController;
//use Illuminate\Http\Response;
//use Illuminate\Http\Request;
//use App\Http\Models\DashboardManagement\DashboardManagementModel;
//use App\Jobs\AudiLog;
//namespace App\Http\Controllers;

use DB;
use App\Http\Models\ProductModel;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CustomDbModel;
use Illuminate\Support\Facades\Storage;


class ProductController extends Controller {

    public function getcategorymaster(Request $request) {
        try {
            $postdata = $request->all();
  
            if ($postdata['action'] == 'add') {

//                 $filepath = 'assets/' . $postdata['file'];
                 $filepath = $postdata['file'];
                 
                  $contracts = explode('base64,', $postdata['file']);
                  pre($contracts);
                        if (isset($contracts) && count($contracts) > 0) {
                            $contracts = Storage::disk('local')->put('category', base64_decode($contracts[0]));
                        }
                        
                // Storage::disk('local')->putFile('category', base64_decode($filepath[1]));
//                 Storage::putFile('photos', new File('/path/to/photo'));

                 pr($filepath->name);exit;
                //Storage::put($filepath, $resource);
                
                $insert['cat_name'] = ucwords($postdata['cat_name']);
                $insert['img'] = $filepath;
                CustomDbModel::insertquery('category_prod', $insert, true);
                return parent::jsonresSuccess(array("message" => 'Added successfully'));
            } elseif ($postdata['action'] == 'edit') {
    
                if (isset($postdata['id']) && !empty($postdata['id'])) {
                    $id = $postdata['id'];
                    $insert['cat_name'] = ucwords($postdata['cat_name']);
                    $insert['img'] = $postdata['img'];

                    $updatedresult = CustomDbModel::updatequery('category_prod', $insert, array("id" => $id));
                    return parent::jsonresSuccess(array("message" => 'Updated successfully'));
                } else {
                    return parent::jsonresError(array("message" => 'Invalid Input'));
                }
            } elseif ($postdata['action'] == 'list') {

                if (isset($postdata['id']) && !empty($postdata['id'])) {
                    $id = $postdata['id'];
                    $condition = array("id" => $id);
                    $categorydetails = CustomDbModel::selectquery('category_prod', array('id', 'cat_name', 'img', 'sort', 'status'), $condition, true);
                } else {
                    $categorydetails = CustomDbModel::selectquery('category_prod', array('id', 'cat_name', 'img', 'sort', 'status'), array());
                }

                return parent::jsonresSuccess(array("categorydetails" => $categorydetails));
            } elseif ($postdata['action'] == 'statusupdate') {
                if (isset($postdata['id']) && !empty($postdata['id'])) {
                    $id = $postdata['id'];
                    $insert['status'] = $postdata['status'];
                    $updatedresult = CustomDbModel::updatequery('category_prod', $insert, array("id" => $id));
                }
            }
        } catch (\Exception $ex) {
//            return parent::jsonresServerError($ex);
            parent::jsonresServerError($ex);
            return parent::jsonresSuccess(array("message" => 'Error info '));
        }
    }

}
