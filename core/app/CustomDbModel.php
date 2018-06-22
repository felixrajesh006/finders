<?php

//
//  This is a CustomDbModel which is used to access the Database. Following are the Function used in it.
//    gridView                  -- To handle grid table data.
//    selectquery               -- To select a single table with condition.
//    selectquerywithcustom     -- To select a multiple table with condition and leftjoin.
//    dropdownquery             -- To gendrate an dropdown value as we needed.
//    insertquery               -- To Insert record to an table.
//    updatequery               -- To Update record to an table using where conditon.
//    updatequeryusingcustom    -- To Update record to an table using like and where raw.
//    rowCheckquery             -- To check if the record is available.
//    deletequery               -- To Delete an record in table or change the status.
//    putsessionuser            -- To add the user data in session table.
//    getsessionuser            -- To get the user data from session table.
//    
//  Model               : CustomDbModel
//  Author              : Jeya Prakash
//  Project             : 2iequip
//

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Crypt;
use Cache;
use Mail;

class CustomDbModel extends Model {

    
    public static function insertquery($tablename, $input, $multipeinsert = false, $insertcompanyid = true) {
      

        if ($multipeinsert == false) {
//            $input['created_on'] = createddatetime();
//            $input['updated_on'] = createddatetime();
            $query = DB::table($tablename)->insertGetId($input);
        } else
            $query = DB::table($tablename)->insert($input);

$camel = camel_case('foo_bar');
        if ($query > 0)
            return $query;
        else
            return null;
    }
    
    //    Select Query with single table
    public static function selectquery($tablename, $select = array(), $condition_array = array(), $singleselect = false, $casefilter = array()) {
        $response = array();

        $query = DB::table($tablename);
       
        foreach ($condition_array as $col => $colvalue)
            $query->where($col, $colvalue);

        if (isset($casefilter) && count($casefilter)) {
            foreach ($casefilter as $incol => $invalue) {
                $query->where($incol, 'ilike', strtolower($invalue));
            }
        }

        if (count($select))
            $query->select($select);

        $query->orderBy('id', 'asc');

        if (!$singleselect) {
            $result = $query->get();
            foreach ($result as $key => $value) {
                $response[$key] = $value;
            }
        } else {
            $response = $query->first();
        }

        if ($response)
            return $response;
        else
            return array();
    }
    
     public static function updatequery($tablename, $input, $condition_array = array(), $condition_arraywhere_in = array(), $updateddate = true, $auditLog = true) {

        $select = array_keys($input);
     
        if ($updateddate == true)
            $input['updated_on'] = createddatetime();


        $query = DB::table($tablename);
        foreach ($condition_array as $col => $colvalue)
            $query->where($col, $colvalue);

        foreach ($condition_arraywhere_in as $col => $colvalue)
            $query->whereIn($col, $colvalue);

        $result = $query->update($input);

        if ($result) {
            return $result;
        } else
            return null;
    }
    
//     Select query with custom where, like, leftjoin
    public static function selectquerywithcustom($tablename, $customquery = array(), $usecompany = true) {
        $query = DB::table($tablename);
       
        if (isset($customquery['like']) && count($customquery['like']))
            foreach ($customquery['like'] as $likecol => $likecolvalue)
                $query->where($likecol, 'Like', '%' . quotereplace($likecolvalue) . '%');

        if (isset($customquery['whereraw']) && count($customquery['whereraw']))
            foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue)
                $query->whereRaw($rawcolvalue);



        if (isset($customquery['casecheck']) && count($customquery['casecheck'])) {
            foreach ($customquery['casecheck'] as $incol => $invalue) {
                $query->where($incol, 'ilike', strtolower($invalue));
            }
        }

        if (isset($customquery['selectcol']) && count($customquery['selectcol']))
            $query->select($customquery['selectcol']);

        if (isset($customquery['sorting']))
            foreach ($customquery['sorting'] as $key => $value)
                $query->orderBy($key, $value);

        if (isset($customquery['condition']) && count($customquery['condition']))
            foreach ($customquery['condition'] as $col => $colvalue)
                $query->where($col, $colvalue);

        if (isset($customquery['where_in']) && count($customquery['where_in'])) {
            foreach ($customquery['where_in'] as $incol => $invalue) {
                $query->whereIn($incol, $invalue);
            }
        }

        if (isset($customquery['where_not_in']) && count($customquery['where_not_in'])) {
            foreach ($customquery['where_not_in'] as $incol => $invalue) {
                $query->whereNotIn($incol, $invalue);
            }
        }
        if (isset($customquery['where_between']) && count($customquery['where_between']))
            foreach ($customquery['where_between'] as $betweencol => $betweenvalue)
                $query->whereBetween($betweencol, $betweenvalue);

        if (isset($customquery['leftjoinwithand']) && count($customquery['leftjoinwithand'])) {
            foreach ($customquery['leftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
                $query->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
                    foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
                        $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
                    }
                });
            }
        }

        if (isset($customquery['leftjoin']) && count($customquery['leftjoin'])) {
            foreach ($customquery['leftjoin'] as $leftjoinkey => $leftjoinvalue) {
                $query->leftJoin($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
            }
        }

        if (isset($customquery['join']) && count($customquery['join'])) {
            foreach ($customquery['join'] as $leftjoinkey => $leftjoinvalue) {
                $query->join($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
            }
        }


        if (isset($customquery['afterleftjoinwithand']) && count($customquery['afterleftjoinwithand'])) {
            foreach ($customquery['afterleftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
                $query->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
                    foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
                        $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
                    }
                });
            }
        }

        if (isset($customquery['rightjoin']) && count($customquery['rightjoin']))
            foreach ($customquery['rightjoin'] as $rightjoinkey => $rightjoinvalue)
                $query->rightJoin($rightjoinvalue[0], $rightjoinvalue['1'], $rightjoinvalue['2'], $rightjoinvalue['3']);

        if (isset($customquery['count']))
            $query->limit($customquery['count']);
        if (isset($customquery['distinct']))
            $query->distinct($customquery['distinct']);

        if (isset($customquery['groupby']))
            $query->groupBy($customquery['groupby']);

        if (isset($customquery['singleslect']) && $customquery['singleslect'] == false) {
            $result = $query->get();
            foreach ($result as $key => $value) {
                $response[$key] = $value;
                if (isset($response[$key]->id)) {
                    $response[$key]->id = Crypt::encrypt($value->id);
                    foreach ($value as $k => $v) {
                        $impoldvalue = explode('_', $k);
                        if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
                            if (isset($v) && $v != '')
                                $response[$key]->$k = datetoapi($v);
                        if (isset($impoldvalue[1]) && $impoldvalue[1] == 'datetime')
                            if (isset($v) && $v != '')
                                $response[$key]->$k = datetimetoapi($v);
                    }
                }
            }
        } else {
            $response = $query->first();
            if (isset($response->id))
                $response->id = Crypt::encrypt($response->id);
        }

        if (isset($response))
            return $response;
        else
            return array();
    }
    
//    public static function getClientID() {
//        $authorizer = app('oauth2-server.authorizer');
//        $authorizer->validateAccessToken();
//        $getauthusderid = $authorizer->getResourceOwnerId();
//        $getClientID = DB::table('users')->where('id', $getauthusderid)->select(array('id', 'fk_company_id'))->first();
//        if (isset($getClientID) && !empty($getClientID)) {
//            return $getClientID->fk_company_id;
//        } else {
//            return 0;
//        }
//    }
//
////    Ngtable Model
//    public static function gridView($tablename, $input, $usecompany = true) {
//
//        $griddataobj = DB::table($tablename);
//        if ($usecompany) {
//            $griddataobj->where($tablename . '.fk_company_id', CustomDbModel::getClientID());
//        }
//
//        if (isset($input['filter']))
//            foreach ($input['filter'] as $key => $value) {
//                if (isset($value) && !empty($value)) {
//                    $impoldvalue = explode('_', $key);
//                    $filterArray = array();
//                    if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date') {
//                        $griddataobj->where($key, "LIKE", "%" . datetodb($value) . "%");
//                    } else {
//                        if (is_array($value)) {
//                            foreach ($value as $val) {
//                                if (is_int($val)) {
//                                    $filterArray[] = $val;
//                                }
//                            }
//                            if (!empty($filterArray)) {
//                                $griddataobj->whereIn($key, $value);
//                            }
//                        } else if (is_int($value)) {
//                            $griddataobj->where($key, $value);
//                        } else {
//                            $key = '"' . str_replace(".", '"."', $key) . '"';
//                            $value = str_replace(array('\'', '"'), '', $value);
//                            $griddataobj->whereRaw(" LOWER(" . $key . ") Like LOWER( '%" . quotereplace($value) . "%' )");
//                        }
//                    }
//                }
//            }
//
//        if (isset($input['customfiltervalue'])) {
//            $customquery = $input['customfiltervalue'];
//            if (isset($customquery['condition']) && count($customquery['condition'])) {
//                foreach ($customquery['condition'] as $col => $colvalue) {
//                    $griddataobj->where($col, $colvalue);
//                }
//            }
//
//            if (isset($customquery['like']) && count($customquery['like'])) {
//                foreach ($customquery['like'] as $likecol => $likecolvalue) {
//                    $griddataobj->where($likecol, 'Like', '%' . quotereplace($likecolvalue) . '%');
//                }
//            }
//
//            if (isset($customquery['whereraw']) && count($customquery['whereraw'])) {
//                foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue) {
//                    $griddataobj->whereRaw($rawcolvalue);
//                }
//            }
//
//            if (isset($customquery['where_in']) && count($customquery['where_in'])) {
//                foreach ($customquery['where_in'] as $incol => $invalue) {
//                    $griddataobj->whereIn($incol, $invalue);
//                }
//            }
//
//            if (isset($customquery['or_where_in']) && count($customquery['or_where_in'])) {
//                foreach ($customquery['or_where_in'] as $incol => $invalue) {
//                    $griddataobj->orWhereIn($incol, $invalue);
//                }
//            }
//
//            if (isset($customquery['where_not_in']) && count($customquery['where_not_in'])) {
//                foreach ($customquery['where_not_in'] as $incol => $invalue) {
//                    $griddataobj->whereNotIn($incol, $invalue);
//                }
//            }
//
//            if (isset($customquery['where_between']) && count($customquery['where_between'])) {
//                foreach ($customquery['where_between'] as $betweencol => $betweenvalue) {
//                    $griddataobj->whereBetween($betweencol, $betweenvalue);
//                }
//            }
//
//            if (isset($customquery['leftjoinwithand']) && count($customquery['leftjoinwithand'])) {
//                foreach ($customquery['leftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
//                    $griddataobj->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
//                        foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
//                            $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
//                        }
//                    });
//                }
//            }
//
//            if (isset($customquery['leftjoin']) && count($customquery['leftjoin'])) {
//                foreach ($customquery['leftjoin'] as $leftjoinkey => $leftjoinvalue) {
//                    $griddataobj->leftJoin($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//                }
//            }
//
//            if (isset($customquery['afterleftjoinwithand']) && count($customquery['afterleftjoinwithand'])) {
//                foreach ($customquery['afterleftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
//                    $griddataobj->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
//                        foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
//                            $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
//                        }
//                    });
//                }
//            }
//
//            if (isset($customquery['join']) && count($customquery['join'])) {
//                foreach ($customquery['join'] as $leftjoinkey => $leftjoinvalue) {
//                    $griddataobj->join($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//                }
//            }
//
//            if (isset($customquery['selectcol']) && count($customquery['selectcol'])) {
//                $griddataobj->select($customquery['selectcol']);
//            }
//
//            if (isset($customquery['groupby'])) {
//                $griddataobj->groupBy($customquery['groupby']);
//            }
//
//            if (isset($customquery['sortingraw']) && !empty($customquery['sortingraw'])) {
//                $griddataobj->orderBy($customquery['sortingraw']);
//            }
//        }
//
//        if (isset($input['limit'])) {
//            $griddataobj->take($input['limit']);
//            $griddata['params']['total'] = $input['limit'];
//        } else {
//            if (!isset($customquery['distinct'])) {
//                $griddata['params']['total'] = $griddataobj->get()->count();
//            } else {
//                $griddata['params']['total'] = $griddataobj->distinct()->get()->count();
//            }
//            if (isset($input['count']))
//                $griddataobj->limit($input['count']);
//
//            if (isset($input['page'])) {
//                $gridpage = $input['count'] * ($input['page'] - 1);
//                $griddataobj->offset($gridpage);
//            }
//        }
//
//        if (isset($input['sorting'])) {
//            foreach ($input['sorting'] as $key => $value)
//                $griddataobj->orderBy($key, $value);
//        }
//
//
//        if (!isset($customquery['distinct'])) {
//            $griddata['list'] = $griddataobj->get();
//        } else {
//            $griddata['list'] = $griddataobj->distinct()->get();
//        }
//
//
//        foreach ($griddata['list'] as $key => $value) {
//            $griddata['list'][$key]->sno = (isset($gridpage) ? $gridpage * 1 : 0 ) + ($key + 1);
//            if (isset($value->id)) {
//                $griddata['list'][$key]->id = Crypt::encrypt($value->id);
//            }
//
//            if (isset($griddata['list'][$key]->status))
//                $griddata['list'][$key]->status = ($value->status) ? TRUE : FALSE;
//
//            foreach ($value as $k => $v) {
//                $impoldvalue = explode('_', $k);
//                if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
//                    if (isset($v) && $v != '')
//                        $griddata['list'][$key]->$k = datetoapi($v);
//                if (isset($impoldvalue[1]) && $impoldvalue[1] == 'datetime')
//                    if (isset($v) && $v != '')
//                        $griddata['list'][$key]->$k = datetimetoapi($v);
//            }
//        }
//        return $griddata;
//    }
//
////    Select Query with single table
//    public static function selectquery($tablename, $select = array(), $condition_array = array(), $singleselect = false, $casefilter = array(), $usecompany = true) {
//        $response = array();
//
//        $query = DB::table($tablename);
//        if ($usecompany) {
//            $query->where($tablename . '.fk_company_id', CustomDbModel::getClientID());
//        }
//
//        foreach ($condition_array as $col => $colvalue)
//            $query->where($col, $colvalue);
//
//        if (isset($casefilter) && count($casefilter)) {
//            foreach ($casefilter as $incol => $invalue) {
//                $query->where($incol, 'ilike', strtolower($invalue));
//            }
//        }
//
//        if (count($select))
//            $query->select($select);
//
//        $query->orderBy('id', 'asc');
//
//        if (!$singleselect) {
//            $result = $query->get();
//            foreach ($result as $key => $value) {
//                $response[$key] = $value;
//                if (isset($value->id))
//                    $response[$key]->id = Crypt::encrypt($value->id);
//            }
//        } else {
//            $response = $query->first();
//            if (isset($response->id))
//                $response->id = Crypt::encrypt($response->id);
//        }
//
//        if ($response)
//            return $response;
//        else
//            return array();
//    }
//
////    Select query with custom where, like, leftjoin
//    public static function selectquerywithcustom($tablename, $customquery = array(), $usecompany = true) {
//        $query = DB::table($tablename);
//        if ($usecompany) {
//            $query->where($tablename . '.fk_company_id', CustomDbModel::getClientID());
//        }
//
//        if (isset($customquery['like']) && count($customquery['like']))
//            foreach ($customquery['like'] as $likecol => $likecolvalue)
//                $query->where($likecol, 'Like', '%' . quotereplace($likecolvalue) . '%');
//
//        if (isset($customquery['whereraw']) && count($customquery['whereraw']))
//            foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue)
//                $query->whereRaw($rawcolvalue);
//
//
//
//        if (isset($customquery['casecheck']) && count($customquery['casecheck'])) {
//            foreach ($customquery['casecheck'] as $incol => $invalue) {
//                $query->where($incol, 'ilike', strtolower($invalue));
//            }
//        }
//
//        if (isset($customquery['selectcol']) && count($customquery['selectcol']))
//            $query->select($customquery['selectcol']);
//
//        if (isset($customquery['sorting']))
//            foreach ($customquery['sorting'] as $key => $value)
//                $query->orderBy($key, $value);
//
//        if (isset($customquery['condition']) && count($customquery['condition']))
//            foreach ($customquery['condition'] as $col => $colvalue)
//                $query->where($col, $colvalue);
//
//        if (isset($customquery['where_in']) && count($customquery['where_in'])) {
//            foreach ($customquery['where_in'] as $incol => $invalue) {
//                $query->whereIn($incol, $invalue);
//            }
//        }
//
//        if (isset($customquery['where_not_in']) && count($customquery['where_not_in'])) {
//            foreach ($customquery['where_not_in'] as $incol => $invalue) {
//                $query->whereNotIn($incol, $invalue);
//            }
//        }
//        if (isset($customquery['where_between']) && count($customquery['where_between']))
//            foreach ($customquery['where_between'] as $betweencol => $betweenvalue)
//                $query->whereBetween($betweencol, $betweenvalue);
//
//        if (isset($customquery['leftjoinwithand']) && count($customquery['leftjoinwithand'])) {
//            foreach ($customquery['leftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
//                $query->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
//                    foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
//                        $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
//                    }
//                });
//            }
//        }
//
//        if (isset($customquery['leftjoin']) && count($customquery['leftjoin'])) {
//            foreach ($customquery['leftjoin'] as $leftjoinkey => $leftjoinvalue) {
//                $query->leftJoin($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//            }
//        }
//
//        if (isset($customquery['join']) && count($customquery['join'])) {
//            foreach ($customquery['join'] as $leftjoinkey => $leftjoinvalue) {
//                $query->join($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//            }
//        }
//
//
//        if (isset($customquery['afterleftjoinwithand']) && count($customquery['afterleftjoinwithand'])) {
//            foreach ($customquery['afterleftjoinwithand'] as $leftjoinkey => $leftjoinvalue) {
//                $query->leftJoin($leftjoinvalue[0], function($join) use ($leftjoinvalue) {
//                    foreach ($leftjoinvalue[1] as $leftjoinwithandkey => $leftjoinwithandvalue) {
//                        $join->on($leftjoinwithandvalue[0], $leftjoinwithandvalue['1'], $leftjoinwithandvalue['2']);
//                    }
//                });
//            }
//        }
//
//        if (isset($customquery['rightjoin']) && count($customquery['rightjoin']))
//            foreach ($customquery['rightjoin'] as $rightjoinkey => $rightjoinvalue)
//                $query->rightJoin($rightjoinvalue[0], $rightjoinvalue['1'], $rightjoinvalue['2'], $rightjoinvalue['3']);
//
//        if (isset($customquery['count']))
//            $query->limit($customquery['count']);
//        if (isset($customquery['distinct']))
//            $query->distinct($customquery['distinct']);
//
//        if (isset($customquery['groupby']))
//            $query->groupBy($customquery['groupby']);
//
//        if (isset($customquery['singleslect']) && $customquery['singleslect'] == false) {
//            $result = $query->get();
//            foreach ($result as $key => $value) {
//                $response[$key] = $value;
//                if (isset($response[$key]->id)) {
//                    $response[$key]->id = Crypt::encrypt($value->id);
//                    foreach ($value as $k => $v) {
//                        $impoldvalue = explode('_', $k);
//                        if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
//                            if (isset($v) && $v != '')
//                                $response[$key]->$k = datetoapi($v);
//                        if (isset($impoldvalue[1]) && $impoldvalue[1] == 'datetime')
//                            if (isset($v) && $v != '')
//                                $response[$key]->$k = datetimetoapi($v);
//                    }
//                }
//            }
//        } else {
//            $response = $query->first();
//            if (isset($response->id))
//                $response->id = Crypt::encrypt($response->id);
//        }
//
//        if (isset($response))
//            return $response;
//        else
//            return array();
//    }
//
////    dropdown Query
//    public static function dropdownquery($tablename, $select = array(), $customquery = array(), $usecompany = true) {
//
//        $query = DB::table($tablename);
//        if ($usecompany) {
//            $query->where($tablename . '.fk_company_id', CustomDbModel::getClientID());
//        }
//
//        if (isset($customquery['condition']) && count($customquery['condition']))
//            foreach ($customquery['condition'] as $col => $colvalue)
//                $query->where($col, $colvalue);
//
//        if (isset($customquery['casecheck']) && count($customquery['casecheck'])) {
//            foreach ($customquery['casecheck'] as $incol => $invalue) {
//                $query->where($incol, 'ilike', strtolower($invalue));
//            }
//        }
//
//        if (isset($customquery['like']) && count($customquery['like'])) {
//            foreach ($customquery['like'] as $likecol => $likecolvalue) {
//                $likecol = DB::getTablePrefix() . str_replace(".", '.', $likecol);
//                $query->whereRaw(" LOWER(" . $likecol . ") Like LOWER( '%" . quotereplace($likecolvalue) . "%' )");
//            }
//        }
//
//        if (isset($customquery['where_in']) && count($customquery['where_in'])) {
//            foreach ($customquery['where_in'] as $incol => $invalue) {
//                $query->whereIn($incol, $invalue);
//            }
//        }
//
//        if (isset($customquery['where_not_in']) && count($customquery['where_not_in'])) {
//            foreach ($customquery['where_not_in'] as $incol => $invalue) {
//                $query->whereNotIn($incol, $invalue);
//            }
//        }
//
//        if (isset($customquery['whereraw']) && count($customquery['whereraw']))
//            foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue)
//                $query->whereRaw($rawcolvalue);
//
//        if (isset($customquery['leftjoin']) && count($customquery['leftjoin']))
//            foreach ($customquery['leftjoin'] as $leftjoinkey => $leftjoinvalue)
//                $query->leftJoin($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//
//        if (isset($customquery['groupby']))
//            $query->groupBy($customquery['groupby']);
//
//        if (isset($select))
//            $query->select($select);
//
//        $result = $query->get();
//
//        if ($result)
//            return $result;
//        else
//            return array();
//    }
//
////    Insert Query
//
//    public static function insertquery($tablename, $input, $multipeinsert = false, $insertcompanyid = true) {
//        if ($insertcompanyid == true && $multipeinsert == false) {
//            $input['fk_company_id'] = CustomDbModel::getClientID();
//        }
//
//        if ($multipeinsert == false) {
//            $input['created_on'] = createddatetime();
//            $input['updated_on'] = createddatetime();
//            $query = DB::table($tablename)->insertGetId($input);
//        } else
//            $query = DB::table($tablename)->insert($input);
//
//
//        if ($query > 0)
//            return $query;
//        else
//            return null;
//    }
//
////  update Query
//

//
//// Increatemnt Count Query 
//    public static function incrementquery($tablename, $input, $customquery = array()) {
//
//        $query = DB::table($tablename);
//
//        if (isset($customquery['condition']) && count($customquery['condition']))
//            foreach ($customquery['condition'] as $col => $colvalue)
//                $query->where($col, $colvalue);
//
//        $result = $query->increment($input);
//
//        if ($result)
//            return $result;
//        else
//            return 0;
//    }
//
////    Update  Query using where and like
//
//    public static function updatequeryusingcustom($tablename, $input, $customquery = array()) {
//
//        $input['updated_on'] = createddatetime();
//
//        $query = DB::table($tablename);
//
//        if (isset($customquery['condition']) && count($customquery['condition']))
//            foreach ($customquery['condition'] as $col => $colvalue)
//                $query->where($col, $colvalue);
//
//        if (isset($customquery['like']) && count($customquery['like']))
//            foreach ($customquery['like'] as $likecol => $likecolvalue)
//                $query->where($likecol, 'Like', '%' . quotereplace($likecolvalue) . '%');
//
//        if (isset($customquery['whereraw']) && count($customquery['whereraw']))
//            foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue)
//                $query->whereRaw($rawcolvalue);
//
//        if (isset($customquery['wherein']) && count($customquery['wherein']))
//            foreach ($customquery['wherein'] as $incol => $incolvalue)
//                $query->whereIn($incol, $incolvalue);
//
//        $result = $query->update($input);
//
//        if ($result)
//            return $result;
//        else
//            return 0;
//    }
//
////    Check if data available Query
//    public static function rowCheckquery($tableName, $customquery = array(), $usecompany = true) {
//
//        $query = DB::table($tableName);
//        if ($usecompany) {
//            $query->where($tableName . '.fk_company_id', CustomDbModel::getClientID());
//        }
//
//        if (isset($customquery['condition']) && count($customquery['condition']))
//            foreach ($customquery['condition'] as $col => $colvalue)
//                $query->where($col, $colvalue);
//
//        if (isset($customquery['like']) && count($customquery['like']))
//            foreach ($customquery['like'] as $likecol => $likecolvalue)
//                $query->where($likecol, 'Like', '%' . quotereplace($likecolvalue) . '%');
//
//        if (isset($customquery['whereraw']) && count($customquery['whereraw']))
//            foreach ($customquery['whereraw'] as $rawcol => $rawcolvalue)
//                $query->whereRaw($rawcolvalue);
//
//        if (isset($customquery['where_in']) && count($customquery['where_in'])) {
//            foreach ($customquery['where_in'] as $incol => $invalue) {
//                $query->whereIn($incol, $invalue);
//            }
//        }
//
//        if (isset($customquery['casecheck']) && count($customquery['casecheck'])) {
//            foreach ($customquery['casecheck'] as $incol => $invalue) {
//                $query->where($incol, 'ilike', strtolower($invalue));
//            }
//        }
//
//        if (isset($customquery['where_not_in']) && count($customquery['where_not_in'])) {
//            foreach ($customquery['where_not_in'] as $incol => $invalue) {
//                $query->whereNotIn($incol, $invalue);
//            }
//        }
//
//        if (isset($customquery['groupby']))
//            $query->groupBy($customquery['groupby']);
//
//        if (isset($customquery['leftjoin']) && count($customquery['leftjoin']))
//            foreach ($customquery['leftjoin'] as $leftjoinkey => $leftjoinvalue)
//                $query->leftJoin($leftjoinvalue[0], $leftjoinvalue['1'], $leftjoinvalue['2'], $leftjoinvalue['3']);
//
//        if (isset($customquery['select']))
//            $query->select($customquery['select']);
//
//        $result = $query->count();
//
//        if ($result > 0) {
//            if (isset($customquery['retrunrowcount']) && $customquery['retrunrowcount'] == true)
//                return $result;
//            else
//                return true;
//        }else {
//            if (isset($customquery['retrunrowcount']) && $customquery['retrunrowcount'] == true)
//                return 0;
//            else
//                return false;
//        }
//    }
//
////    Delete Query
//    public static function deletequery($tableName, $condition_array = array(), $deletestatus = false) {
//
//        $query = DB::table($tableName);
//        foreach ($condition_array as $col => $colvalue)
//            $query->where($col, $colvalue);
//
//        if ($deletestatus == true)
//            $result = $query->delete();
//        else
//            $result = $query->update(array('dels' => 1));
//
//        if ($result)
//            return true;
//        else
//            return false;
//    }
//
////    Add Session User
//    public static function putsessionuser($sessionkey, $sesssionuser) {
//        $query = DB::table('users_session')->insert(array('session_key' => $sessionkey, 'session_value' => json_encode($sesssionuser), 'created_on' => createddatetime(), 'updated_on' => createddatetime()));
//        if ($query)
//            return true;
//        else
//            return false;
//    }
//
////    Get Session User
//    public static function getsessionuser($userid) {
//        $users = DB::table('users')->where('id', $userid)->where('forgot_password', '')->where('status', 1)->where('dels', 0)->select(array('id', 'user_type', 'emailid', DB::raw(' CONCAT(user_firstname, \' \', user_lastname) AS username'), 'language_preference'))->first();
//
//        if (isset($users->id) && $users->id) {
//            $users->usermaping = DB::table('user_mapping')->where('fk_users_id', $userid)->select(array('company', 'manufacturer', 'serviceagency', 'standaloneserviceagency', 'customer', 'serviceengineer', 'staff'))->first();
//            $users->settings = DB::table('company')->where('id', $users->usermaping->company)->select(array('id', 'company_name', 'logo', 'email_preference', 'sms_preference', 'push_notification_preference', 'notification_type', 'language_preference', 'time_zone', 'currency_type', 'features_workorder', 'features_contract', 'features_generic_ticket', 'time_zone'))->first();
//
//            if (($users->user_type == config('2iequipstatus.usertype.manufacturer') || $users->user_type == config('2iequipstatus.usertype.standaloneserviceagency')) && isset($users->usermaping->company) || $users->user_type == config('2iequipstatus.usertype.loactionhead') || $users->user_type == config('2iequipstatus.usertype.machineowner')) {
//                if ($users->user_type == config('2iequipstatus.usertype.standaloneserviceagency')) {
//                    $serviceagencyDetatils = DB::table('serviceagency')->where('id', $users->usermaping->serviceagency)->select(array('agency_name'))->first();
//                    if (isset($serviceagencyDetatils->agency_name)) {
//                        $users->settings->agency_name = $serviceagencyDetatils->agency_name;
//                    }
//                }
//            }
//
//            $users->settings = DB::table('company')->where('id', $users->usermaping->company)->select(array('company_name', 'logo', 'email_preference', 'sms_preference', 'push_notification_preference', 'notification_type', 'language_preference', 'time_zone', 'currency_type', 'features_workorder', 'features_contract', 'features_generic_ticket', 'tentative_time'))->first();
//
//
//            if ($users->user_type == config('2iequipstatus.usertype.serviceexecutive')) {
//                $serviceexecutive_customers = DB::table('executive_customers_mapping')->where('fk_company_id', $users->usermaping->company)->where('fk_users_id', $users->id)->where('status', 1)->where('dels', 0)->select(array('fk_customers_id'))->get();
//                $customerlist = array();
//                if (count($serviceexecutive_customers)) {
//                    foreach ($serviceexecutive_customers as $key => $value) {
//                        $customerlist[] = $value->fk_customers_id;
//                    }
//                }
//                $users->serviceexecutive_customers = $customerlist;
//            }
//
//            if (isset($users->language_preference) && $users->language_preference != '') {
//                $users->language = $users->language_preference;
//            } else {
//                $users->language = $users->settings->language_preference;
//            }
//
//            if ($users->user_type == config('2iequipstatus.usertype.serviceagency')) {
//                $serviceagency_id = DB::table('serviceagency_users_mapping')->where('fk_company_id', $users->usermaping->company)->where('fk_users_id', $users->id)->select(array('fk_serviceagency_id as id'))->first();
//                if (isset($serviceagency_id)) {
//                    $users->serviceagency_id = $serviceagency_id->id;
//                    $users->contractormapped = true;
//                } else {
//                    $users->contractormapped = false;
//                }
//            }
//
//            if ($users->user_type == config('2iequipstatus.usertype.customer')) {
//                $customer_id = DB::table('customers_users_mapping')->where('fk_company_id', $users->usermaping->company)->where('status', 1)->where('dels', 0)->where('fk_users_id', $users->id)->select(array('fk_customers_id as id'))->first();
//                if (isset($customer_id)) {
//                    $users->customer_id = $customer_id->id;
//                    $users->customermapped = true;
//                } else {
//                    $users->customermapped = false;
//                }
//            }
//
//            if (isset($users->usermaping->company)) {
//                $business_type = DB::table('company')->where('id', $users->usermaping->company)->select(array('fk_business_types_id', 'expiry_date', 'status', 'features_workorder', 'features_contract', 'features_generic_ticket'))->first();
//                $users->business_type = $business_type->fk_business_types_id;
//            }
//
//            if ($users->business_type == config('2iequipstatus.businesstype.equipment_user') && (($users->user_type == config('2iequipstatus.usertype.loactionhead') || $users->user_type == config('2iequipstatus.usertype.machineowner') || $users->user_type == config('2iequipstatus.usertype.appuser') || $users->user_type == config('2iequipstatus.usertype.serviceengineer')))) {
//                $getLocation = true;
//                $is_global_serviceengineer = CustomDbModel::selectquery('service_engineer', array('is_global_serviceengineer'), array('fk_users_id' => $userid, 'fk_company_id' => $users->usermaping->company), true, array(), false);
//                if ($users->user_type == config('2iequipstatus.usertype.serviceengineer') && isset($is_global_serviceengineer->is_global_serviceengineer) && $is_global_serviceengineer->is_global_serviceengineer == 1) {
//                    $getLocation = false;
//                }
//                if ($getLocation) {
//                    $location = DB::table('customers_locations_users_mapping')->where('customers_locations_users_mapping.fk_company_id', $users->usermaping->company)->where('customers_locations_users_mapping.fk_customers_id', $users->usermaping->customer)->where('customers_locations_users_mapping.dels', 0)->where('customers_locations_users_mapping.fk_users_id', $users->id)->where('customers_locations.status', 1)->where('customers_locations.dels', 0)->where('customers_locations_users_mapping.dels', 0)->leftJoin('customers_locations', 'customers_locations.id', '=', 'customers_locations_users_mapping.fk_customers_locations_id')->select(array('customers_locations_users_mapping.fk_customers_locations_id', 'customers_locations_users_mapping.fk_customers_locations_departments_id'))->first();
//                }
//                if (isset($location->fk_customers_locations_id) && $location->fk_customers_locations_id != "") {
//                    $users->usermaping->location = $location->fk_customers_locations_id;
//                    $users->usermaping->department = $location->fk_customers_locations_departments_id;
//                    $users->locationmapped = true;
//                } else {
//                    $users->locationmapped = false;
//                }
//            }
//        }
//
//
//        if (isset($users) && !empty($users)) {
//            return $users;
//        } else {
//            return false;
//        }
//    }
//
//    public static function gridViewNotify($tablename, $input) {
//
//        $griddataobj = DB::table($tablename);
//
//        if (isset($input['sorting']))
//            foreach ($input['sorting'] as $key => $value)
//                $griddataobj->orderBy($key, $value);
//
//        if (isset($input['selectcol']) && count($input['selectcol'])) {
//            foreach ($input['selectcol'] as $key => $value) {
//                $selectarray[] = $value;
//            }
//            $griddataobj->select($selectarray);
//        }
//
//        if (isset($input['condition']) && count($input['condition']))
//            foreach ($input['condition'] as $col => $colvalue)
//                $griddataobj->where($col, $colvalue);
//
//        if (isset($input['whereraw']) && count($input['whereraw'])) {
//            foreach ($input['whereraw'] as $rawcol => $rawcolvalue) {
//                $griddataobj->whereRaw($rawcolvalue);
//            }
//        }
//
//        if (isset($input['where_in']) && count($input['where_in'])) {
//            foreach ($input['where_in'] as $incol => $invalue) {
//                $griddataobj->whereIn($incol, $invalue);
//            }
//        }
//
//        if (isset($input['groupby']))
//            $griddataobj->groupBy($input['groupby']);
//
//        $griddata['params']['total'] = count($griddataobj->get());
//
//        if (isset($input['count']))
//            $griddataobj->limit($input['count']);
//
//        if (isset($input['page'])) {
//            $gridpage = $input['count'] * ($input['page'] - 1);
//            $griddataobj->offset($gridpage);
//        }
//
//        $griddata['list'] = $griddataobj->get();
//
//        foreach ($griddata['list'] as $key => $value) {
//
//            $griddata['list'][$key]->sno = (($gridpage) ? $gridpage * 1 : 0 ) + ($key + 1);
//            if (isset($value->id)) {
//                $griddata['list'][$key]->id = Crypt::encrypt($value->id);
//            }
//
//            if (isset($griddata['list'][$key]->status))
//                $griddata['list'][$key]->status = ($value->status) ? TRUE : FALSE;
//
//            foreach ($value as $k => $v) {
//                $impoldvalue = explode('_', $k);
//                if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
//                    if (isset($v) && $v != '')
//                        $griddata['list'][$key]->$k = datetoapi($v);
//            }
//        }
//        return $griddata;
//    }
//
//// update Batch Notification Count Query 
//    public static function batchnotificationcount($batchnotificaion) {
//        if (isset($batchnotificaion['device_id']) && !empty($batchnotificaion['device_id']))
//            $newdevice_id = $batchnotificaion['device_id'];
//        else
//            $newdevice_id = '';
//
//        switch ($batchnotificaion['user_type']) {
//            case config('2iequipstatus.usertype.manufacturer'): { //Company
//                    $condition_batch = array('user_mobile_batch_notify.parent_id' => $batchnotificaion['parent_id'], "user_mobile_batch_notify.user_type" => config('2iequipstatus.usertype.manufacturer'));
//                    break;
//                }
//            case config('2iequipstatus.usertype.serviceagency'): { //Service Ageny
//                    $condition_batch = array('user_mobile_batch_notify.parent_id' => $batchnotificaion['parent_id'], "user_mobile_batch_notify.user_type" => config('2iequipstatus.usertype.serviceagency'));
//                    break;
//                }
//            case config('2iequipstatus.usertype.customer'): { // Customer
//                    $condition_batch = array('user_mobile_batch_notify.parent_id' => $batchnotificaion['parent_id'], "user_mobile_batch_notify.user_type" => config('2iequipstatus.usertype.customer'));
//                    break;
//                }
//            case config('2iequipstatus.usertype.serviceengineer'): { // Service Engineer
//                    $condition_batch = array('user_mobile_batch_notify.parent_id' => $batchnotificaion['parent_id'], "user_mobile_batch_notify.user_type" => config('2iequipstatus.usertype.serviceengineer'));
//                    break;
//                }
//            case config('2iequipstatus.usertype.appuser'): { // App-user
//                    $condition_batch = array('user_mobile_batch_notify.parent_id' => $batchnotificaion['parent_id'], "user_mobile_batch_notify.user_type" => config('2iequipstatus.usertype.appuser'));
//                    break;
//                }
//        }
//        $postCheckNotify['condition'] = $condition_batch;
//        if (isset($batchnotificaion['fk_users_id']) && !empty($batchnotificaion['fk_users_id'])) {
////$postCheckNotify['condition']['fk_users_id'] = $batchnotificaion['fk_users_id'];
//        }
//
//        $checkNotify = CustomDbModel::rowCheckquery('user_mobile_batch_notify', $postCheckNotify);
//        if (isset($checkNotify) && !empty($checkNotify)) {
//            $newnotify = array(
//                "condition" => array("fk_users_id" => $batchnotificaion['fk_users_id'], "parent_id" => $batchnotificaion['parent_id'], "user_type" => $batchnotificaion['user_type'])
//            );
//            $countofnotification = CustomDbModel::incrementquery('user_mobile_batch_notify', "notification_count", $newnotify);
//        } else {
//            $countofnotification = CustomDbModel::insertquery('user_mobile_batch_notify', array('notification_count' => 1, 'fk_users_id' => $batchnotificaion['fk_users_id'], "parent_id" => $batchnotificaion['parent_id'], "user_type" => $batchnotificaion['user_type']), false);
//        }
//
//        return $countofnotification;
//    }
//
//    public static function sendTicketNotification_old($ticketID, $action, $userdata, $engineerID = NULL, $spareRequestID = NULL, $dispatchedBy = NULL) {
//        try {
//            $userDetails = array();
//            $getTicketDetialsParams = array(
//                'selectcol' => array('ticket.fk_company_id', 'ticket.fk_serviceagency_id', 'ticket.fk_customers_id', 'ticket.fk_users_id', 'ticket.fk_equipment_traceability_id', 'ticket.ticket_id', 'ticket.ticket_type', 'ticket.ticket_comments', 'ticket.ticket_internal_comment', 'ticket.ticket_overdue', 'ticket.ticket_status', 'ticket.working_in_problem', 'ticket.raised_on', 'ticket.created_by', 'ticket.is_schedule_ticket', 'ticket.schedule_id', 'ticket.task_id', 'ticket.task_user_id', 'ticket.fk_customers_locations_id', 'equipment_traceability.fk_equipments_model_id', 'users.user_type', 'users.parent_id', 'equipments_model.is_movable'),
//                'leftjoin' => array(array('equipment_traceability', 'equipment_traceability.id', '=', 'ticket.fk_equipment_traceability_id'), array('users', 'users.id', '=', 'ticket.fk_users_id'), array('equipments_model', 'equipments_model.id', '=', 'equipment_traceability.fk_equipments_model_id')),
//                'condition' => array('ticket.id' => $ticketID, 'ticket.fk_company_id' => $userdata->usermaping->company),
//                'singleslect' => true
//            );
//            $ticketDetails = CustomDbModel::selectquerywithcustom('ticket', $getTicketDetialsParams, false);
//
//            $companyID = $ticketDetails->fk_company_id;
//            $agencyID = $ticketDetails->fk_serviceagency_id;
//            $customerID = $ticketDetails->fk_customers_id;
//            $locationID = $ticketDetails->fk_customers_locations_id;
//
//            if (isset($ticketDetails) && !empty($ticketDetails)) {
//
//                if ($ticketDetails->ticket_type == 1) { // Defect Ticket(Raised Ticket)
//                    $getTicketTypedataParams = array(
//                        "selectcol" => array('defect_ticket.fk_defect_types_id', 'defect_types.defect_type'),
//                        "leftjoin" => array(
//                            array('defect_types', 'defect_types.id', '=', 'defect_ticket.fk_defect_types_id')),
//                        'condition' => array('defect_ticket.fk_ticket_id' => $ticketID, 'defect_ticket.fk_company_id' => $userdata->usermaping->company));
//                    $ticketTypes = CustomDbModel::selectquerywithcustom('defect_ticket', $getTicketTypedataParams, false);
//                    if (isset($ticketTypes->fk_defect_types_id)) {
//                        //$ticketDetails->fk_defect_types_id = $ticketTypes->fk_defect_types_id;
//                        $ticketDetails->ticketFor = $ticketTypes ? $ticketTypes->defect_type : '';
//                    }
//                } else if ($ticketDetails->ticket_type == 2) { // Schedule Ticket
//                    $getTicketTypedataParams = array(
//                        "selectcol" => array('users_schedule.id', 'users_schedule.schedule_reference_id', 'preventive_maintenance.maintenance_name as schedule_name'),
//                        "leftjoin" => array(
//                            array('users_schedule', 'users_schedule.id', '=', 'schedule_ticket.fk_schedule_id'),
//                            array('equipment_schedule_mapping', 'equipment_schedule_mapping.id', '=', 'users_schedule.fk_equipment_schedule_mapping_id'),
//                            array('preventive_maintenance', 'preventive_maintenance.id', '=', 'equipment_schedule_mapping.fk_preventive_maintenance_id')
//                        ),
//                        'condition' => array('schedule_ticket.fk_ticket_id' => $ticketID, 'schedule_ticket.fk_company_id' => $userdata->usermaping->company));
//                    $ticketTypes = CustomDbModel::selectquerywithcustom('schedule_ticket', $getTicketTypedataParams, false);
//                    //$ticketDetails->schedule_id = $ticketTypes->id;
//                    $ticketDetails->ticketFor = $ticketTypes->schedule_name;
//                } else if ($ticketDetails->ticket_type == 3) { // Spare schedule Ticket
//                    $getTicketTypedataParams = array(
//                        "selectcol" => array('spareparts_schedule.id', 'spareparts_schedule.schedule_reference_id', 'equipment_spareparts.spareparts_name', 'equipment_spareparts.severity', 'equipment_spareparts.life_span', 'equipment_spareparts.life_span_period', 'equipment_spareparts.spareparts_id', 'equipment_spareparts.image', 'equipment_spareparts.is_replace'),
//                        "leftjoin" => array(
//                            array('spareparts_schedule', 'spareparts_schedule.id', '=', 'spareparts_ticket.fk_spareparts_schedule_id'),
//                            array('ticket_replacement', 'ticket_replacement.fk_spareparts_ticket_id', '=', 'spareparts_ticket.id'),
//                            array('schedule_replacement', 'schedule_replacement.id', '=', 'ticket_replacement.fk_schedule_replacement_id'),
//                            array('equipment_spareparts', 'equipment_spareparts.id', '=', 'schedule_replacement.fk_equipment_spareparts_id')
//                        ),
//                        'condition' => array('spareparts_ticket.fk_ticket_id' => $ticketID, 'spareparts_ticket.fk_company_id' => $userdata->usermaping->company));
//                    $ticketTypes = CustomDbModel::selectquerywithcustom('spareparts_ticket', $getTicketTypedataParams, false);
//                    //$ticketDetails->schedule_id = $ticketTypes->id;
//                    //$ticketDetails->spareparts_id = $ticketTypes->spareparts_id;
//                    $ticketDetails->ticketFor = $ticketTypes->spareparts_name;
//                } else if ($ticketDetails->ticket_type == 4) {
//                    $ticketDetails->ticketFor = $ticketDetails->ticket_comments;
//                }
//
//                if ($ticketDetails->is_movable == 1) {
//                    $trackingDetails = CustomDbModel::selectquery('ticket_asset_location', array('current_location'), array("fk_ticket_id" => $ticketID, 'ticket_asset_location.fk_company_id' => $userdata->usermaping->company), TRUE, array(), false);
//                } else {
//                    if ($userdata->business_type == config('2iequipstatus.businesstype.equipment_user')) {
//                        $getTrackingParams = array('selectcol' => array('customers_locations.geo_location as current_location'), 'leftjoin' => array(array('customers_locations_asset_mapping', 'customers_locations_asset_mapping.id', '=', 'customers_locations_serialnumber_mapping.fk_customers_locations_asset_mapping_id'), array('customers_locations', 'customers_locations.id', '=', 'customers_locations_asset_mapping.fk_customers_locations_id')),
//                            'condition' => array('customers_locations_serialnumber_mapping.fk_equipment_traceability_id' => $ticketDetails->fk_equipment_traceability_id, 'customers_locations.fk_customers_id' => $ticketDetails->fk_customers_id, 'customers_locations_serialnumber_mapping.dels' => 0, 'customers_locations.dels' => 0, 'customers_locations_serialnumber_mapping.fk_company_id' => $userdata->usermaping->company),
//                            'singleslect' => true
//                        );
//                        $trackingDetails = CustomDbModel::selectquerywithcustom('customers_locations_serialnumber_mapping', $getTrackingParams, false);
//                    } else if ($userdata->business_type == config('2iequipstatus.businesstype.service_agency') && $userdata->user_type == config('2iequipstatus.usertype.serviceengineer')) {
//                        $getTrackingParams = array('selectcol' => array('customers.geo_location as current_location'),
//                            'condition' => array('customers.id' => $ticketDetails->fk_customers_id, 'customers.fk_company_id' => $userdata->usermaping->company),
//                            'singleslect' => true
//                        );
//                        $trackingDetails = CustomDbModel::selectquerywithcustom('customers', $getTrackingParams, false);
//                    } else {
//                        $trackingDetails = CustomDbModel::selectquery('customers_equipment_mapping', array('current_equipment_address as current_location'), array("fk_equipment_traceability_id" => $ticketDetails->fk_equipment_traceability_id, 'fk_customers_id' => $ticketDetails->fk_customers_id, 'dels' => 0, 'customers_equipment_mapping.fk_company_id' => $userdata->usermaping->company), TRUE, array(), false);
//                    }
//                }
//
//                if (isset($action) && !empty($action)) {// Actions
//                    if ($userdata->business_type == config('2iequipstatus.businesstype.service_agency')) {
//                        $getusersDetails = array(
//                            "selectcol" =>
//                            array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                            "leftjoin" => array(
//                                array('users', 'users.id', '=', 'customers_users_mapping.fk_users_id')
//                            ),
//                            "condition" => array('customers_users_mapping.fk_customers_id' => $customerID, 'customers_users_mapping.dels' => 0, 'users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'customers_users_mapping.fk_company_id' => $userdata->usermaping->company),
//                            "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.customer'))),
//                            "singleslect" => false
//                        );
//                        $userDetails = CustomDbModel::selectquerywithcustom('customers_users_mapping', $getusersDetails, false);
//                    } elseif ($userdata->business_type == config('2iequipstatus.businesstype.equipment_user')) {
//                        $getusersDetails = array(
//                            "selectcol" => array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                            "leftjoin" => array(
//                                array('users', 'users.id', '=', 'customers_locations_users_mapping.fk_users_id'),
//                            ),
//                            "condition" => array('customers_locations_users_mapping.fk_customers_locations_id' => $locationID, 'customers_locations_users_mapping.dels' => 0, 'users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'customers_locations_users_mapping.fk_company_id' => $userdata->usermaping->company),
//                            "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.loactionhead'), config('2iequipstatus.usertype.machineowner'))),
//                            "singleslect" => false
//                        );
//                        $userDetails = CustomDbModel::selectquerywithcustom('customers_locations_users_mapping', $getusersDetails, false);
//                    }
//
//                    $getclientUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'fk_company_id' => $companyID, 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.standaloneserviceagency'))),
//                        "singleslect" => false
//                    );
//                    $clientUserDetails = CustomDbModel::selectquerywithcustom('users', $getclientUsersDetails, false);
//                    if (isset($clientUserDetails) && !empty($clientUserDetails)) {
//                        $userDetails = array_merge($userDetails, $clientUserDetails);
//                    }
//
//                    if (isset($engineerID) && !empty($engineerID) && $engineerID != NULL) {
//                        $getEngineerUsersDetails = array(
//                            "selectcol" =>
//                            array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id', 'ticket_engineer.tentative_date_time as tentative_date'),
//                            "leftjoin" => array(array('users', 'users.id', '=', 'service_engineer.fk_users_id'), array('ticket_engineer', 'ticket_engineer.fk_service_engineer_id', '=', 'service_engineer.id')),
//                            "condition" => array('users.dels' => 0, 'service_engineer.id' => $engineerID, 'ticket_engineer.fk_ticket_id' => $ticketID, 'users.status' => 1, 'users.forgot_password' => '', 'service_engineer.fk_company_id' => $userdata->usermaping->company),
////                                "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.servicengineer'), config('2iequipstatus.usertype.machineowner'))),
//                            "singleslect" => true
//                        );
//                        $engineerDetails = CustomDbModel::selectquerywithcustom('service_engineer', $getEngineerUsersDetails, false);
//                        if (isset($engineerDetails) && !empty($engineerDetails)) {
//                            $engineerName = $engineerDetails->firstname . " " . $engineerDetails->lastname;
//                            $tentativeDate = $engineerDetails->tentative_date;
//                            $engDetails[] = $engineerDetails;
//                            $userDetails = array_merge($userDetails, $engDetails);
//                        }
//                    }
//                    $getRaisedUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.id' => $ticketDetails->fk_users_id, 'users.status' => 1, 'users.forgot_password' => '', 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array(),
//                        "singleslect" => true
//                    );
//                    $raisedUserDetails = CustomDbModel::selectquerywithcustom('users', $getRaisedUsersDetails, false);
//                    if (isset($raisedUserDetails) && !empty($raisedUserDetails)) {
//                        $usrDetails[] = $raisedUserDetails;
//                        $userDetails = array_merge($userDetails, $usrDetails);
//                    }
//
//                    if (isset($userDetails) && !empty($userDetails)) {
//                        $NotifyDetails = array();
//                        $groupusermail = array();
//                        foreach ($userDetails as $key => $value) {
//                            $NotifyDetails[$value->usrID] = $value;
//                        }
//                        if (isset($NotifyDetails) && !empty($NotifyDetails)) {
//                            $replaceString = array("replaceTicketID" => isset($ticketDetails->ticket_id) ? $ticketDetails->ticket_id : "", "replaceDefect" => isset($ticketDetails->ticketFor) ? $ticketDetails->ticketFor : "", "replaceUserName" => isset($userdata->username) ? $userdata->username : "", "replaceCompanyName" => isset($userdata->settings->company_name) ? $userdata->settings->company_name : "", "replaceSpareRequestID" => (isset($spareRequestID) && $spareRequestID != NULL) ? $spareRequestID : "", "replaceDispatchedThrough" => (isset($dispatchedBy) && $dispatchedBy != NULL) ? $dispatchedBy : '', "replaceEngineerName" => (isset($engineerName) && !empty($engineerName)) ? $engineerName : "", "replaceLocationName" => isset($trackingDetails->current_location) ? $trackingDetails->current_location : "", "replaceTentativeReachTime" => (isset($tentativeDate) && !empty($tentativeDate)) ? $tentativeDate : "", "replaceTime" => createddatetimeonly());
//                            foreach ($NotifyDetails as $key => $value) {
//                                if ($value->usrID != $userdata->id) {
//                                    $ticketTitle = config('ticketnotifications.actions.' . $action . '.' . $value->user_type . '.title');
//                                    $ticketMessage = config('ticketnotifications.actions.' . $action . '.' . $value->user_type . '.message');
//                                    if (isset($ticketTitle) && isset($ticketMessage) && !empty($ticketTitle) && !empty($ticketMessage)) {
//                                        $notifyTitle = notificationStringReplacement($ticketTitle, $replaceString);
//                                        $notifyMessage = notificationStringReplacement($ticketMessage, $replaceString);
//                                        $notificaion = array("fk_company_id" => $companyID, "notification_id" => $ticketDetails->ticket_id, "notification_title" => trim($notifyTitle), "notification_message" => trim($notifyMessage), "notification_reference_id" => $ticketID, "notification_type" => config('2iequipstatus.notificationtype.ticket'), "parent_id" => $value->parent_id, "user_type" => $value->user_type, 'foruser' => $value->usrID, "fk_users_id" => $value->usrID, "created_by" => $value->usrID, "updated_by" => $value->usrID, "created_on" => createddatetime(), "updated_on" => createddatetime(), "status" => 1, "dels" => 0, 'userdata' => $userdata);
//                                        CustomDbModel::NotifictionAndPush($notificaion);
//                                        if (isset($value->emailid)) {
//                                            $groupusermail[] = $value->emailid;
//                                        }
//                                    }
//                                }
//                            }
//
//                            if (count($groupusermail) > 0) {
//                                $notificationpreference = DB::table('company')->where('id', $notificaion['fk_company_id'])->select(array('push_notification_preference', 'email_preference'))->first();
//
//                                if ($notificationpreference->email_preference == 1) {
//                                    $data = array(
//                                        "url" => str_replace('api/public', '', url('/')) . '/',
//                                        "notificationtitle" => $notificaion['notification_title'],
//                                        "notificationmessage" => $notificaion['notification_message']
//                                    );
//                                    $email = $groupusermail;
//                                    $subject = $notificaion['notification_title'];
//                                    try {
//                                        Mail::send('emails.notification', $data, function ($message) use ($email, $subject) {
//                                            $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//                                            $message->to($email)->subject($subject);
//                                        });
//                                    } catch (\Exception $ex) {
//                                        $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//                                        file_put_contents(env("APP_LOG_PATH") . "/TicketNotification-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        } catch (\Exception $ex) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/TicketNotification-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function sendScheduleTaskNotification($scheduleID, $action, $taskID, $userID, $taskUserID, $userdata) {
//        try {
//            $getScheduleParams = array(
//                "selectcol" => array('users_schedule.fk_company_id', 'users_schedule.fk_customers_id', 'users_schedule.fk_equipment_traceability_id', 'users_schedule.schedule_status', 'users_schedule.schedule_reference_id', 'schedule_check_list_master.checklist_name', 'schedule_check_list_master.task_type', 'schedule_check_list_master.task_sequence', 'users_schedule.current_sequence', 'preventive_maintenance.maintenance_name as schedule_name', DB::raw(' CONCAT(equipments_name, \' \', equipment_model_name) AS asset')),
//                "leftjoin" => array(
//                    array('equipment_schedule_mapping', 'equipment_schedule_mapping.id', '=', 'users_schedule.fk_equipment_schedule_mapping_id'),
//                    array('preventive_maintenance', 'preventive_maintenance.id', '=', 'equipment_schedule_mapping.fk_preventive_maintenance_id'),
//                    array('schedule_check_list_master', 'schedule_check_list_master.fk_preventive_maintenance_id', '=', 'equipment_schedule_mapping.fk_preventive_maintenance_id'),
//                    array('equipment_traceability', 'equipment_traceability.id', '=', 'users_schedule.fk_equipment_traceability_id'),
//                    array('equipments_model', 'equipments_model.id', '=', 'equipment_traceability.fk_equipments_model_id'),
//                    array('equipments', 'equipments.id', '=', 'equipments_model.fk_equipments_id')
//                ),
//                'condition' => array('users_schedule.id' => $scheduleID, 'schedule_check_list_master.id' => $taskID, 'schedule_check_list_master.dels' => 0, 'equipment_schedule_mapping.dels' => 0, 'users_schedule.fk_company_id' => $userdata->usermaping->company),
//                'singlesclet' => true,
//            );
//            $scheduleDetails = CustomDbModel::selectquerywithcustom('users_schedule', $getScheduleParams, false);
//
//            $companyID = $scheduleDetails->fk_company_id;
//            $customerID = $scheduleDetails->fk_customers_id;
//            if (isset($scheduleDetails) && !empty($scheduleDetails)) {
//
//                $businesstype = DB::table('company')->where('id', $userdata->usermaping->company)->select('fk_business_types_id')->first();
//                if (isset($businesstype->fk_business_types_id)) {
//                    $userdata->business_type = $businesstype->fk_business_types_id;
//                }
//                if ($userdata->business_type == config('2iequipstatus.businesstype.equipment_user')) {
//                    $getTrackingParams = array('selectcol' => array('customers_locations.geo_location as current_location'), 'leftjoin' => array(array('customers_locations_asset_mapping', 'customers_locations_asset_mapping.id', '=', 'customers_locations_serialnumber_mapping.fk_customers_locations_asset_mapping_id'), array('customers_locations', 'customers_locations.id', '=', 'customers_locations_asset_mapping.fk_customers_locations_id')),
//                        'condition' => array('customers_locations_serialnumber_mapping.fk_equipment_traceability_id' => $scheduleDetails->fk_equipment_traceability_id, 'customers_locations.fk_customers_id' => $customerID, 'customers_locations_serialnumber_mapping.dels' => 0, 'customers_locations.dels' => 0, 'customers_locations_serialnumber_mapping.fk_company_id' => $userdata->usermaping->company),
//                        'singleslect' => true
//                    );
//                    $trackingDetails = CustomDbModel::selectquerywithcustom('customers_locations_serialnumber_mapping', $getTrackingParams, false);
//                } else if ($userdata->business_type == config('2iequipstatus.businesstype.service_agency') && $userdata->user_type == config('2iequipstatus.usertype.serviceengineer')) {
//                    $getTrackingParams = array('selectcol' => array('customers.geo_location as current_location'),
//                        'condition' => array('customers.id' => $customerID, 'customers.fk_company_id' => $userdata->usermaping->company),
//                        'singleslect' => true
//                    );
//                    $trackingDetails = CustomDbModel::selectquerywithcustom('customers', $getTrackingParams, false);
//                } else {
//                    $trackingDetails = CustomDbModel::selectquery('customers_equipment_mapping', array('current_equipment_address as current_location'), array("fk_equipment_traceability_id" => $scheduleDetails->fk_equipment_traceability_id, 'fk_customers_id' => $customerID, 'dels' => 0, 'customers_equipment_mapping.fk_company_id' => $userdata->usermaping->company), TRUE, array(), false);
//                }
//
//                if (isset($action) && !empty($action)) {// Actions
//                    $getclientUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'fk_company_id' => $companyID, 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.standaloneserviceagency'))),
//                        "singleslect" => false
//                    );
//                    $companyuser = CustomDbModel::selectquerywithcustom('users', $getclientUsersDetails, false);
//
//                    $getRaisedUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.id' => $userID, 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array(),
//                        "singleslect" => false
//                    );
//                    $userDetails = CustomDbModel::selectquerywithcustom('users', $getRaisedUsersDetails, false);
//
//                    if (isset($userDetails) && !empty($userDetails)) {
//                        $userDetails = array_merge($userDetails, $companyuser);
//                        // remove dublication users
//                        if (!empty($userDetails)) {
//                            foreach ($userDetails as $key => $val) {
//                                unset($userDetails[$key]);
//                                $userDetails[$val->usrID] = $val;
//                            }
//                        }
//                        $taskType = config('2iequipstatus.schedule_task_type.' . $scheduleDetails->task_type);
//                        $groupusermail = array();
//                        foreach ($userDetails as $key => $value) {
//
//                            if ($value->usrID != $userdata->id) {
//
//                                $replaceString = array("replaceTaskID" => isset($scheduleDetails->checklist_name) ? $scheduleDetails->checklist_name : '', "replaceTaskName" => isset($scheduleDetails->checklist_name) ? $scheduleDetails->checklist_name : '', "replaceScheduleName" => isset($scheduleDetails->schedule_name) ? $scheduleDetails->schedule_name : '', "replaceScheduleID" => isset($scheduleDetails->schedule_reference_id) ? $scheduleDetails->schedule_reference_id : '', "replaceLocationName" => isset($trackingDetails->current_location) ? $trackingDetails->current_location : "", "replaceUserName" => isset($userdata->username) ? $userdata->username : "", "replaceCompanyName" => isset($userdata->settings->company_name) ? $userdata->settings->company_name : "", "replaceAssetName" => isset($scheduleDetails->asset) ? $scheduleDetails->asset : "", "replaceTaskType" => (isset($taskType) && !empty($taskType)) ? $taskType : "", "replaceEngineerName" => (isset($value->firstname) && isset($value->lastname)) ? $value->firstname . ' ' . $value->lastname : "");
//
//                                $title = config('schedulenotifications.scheduletask.' . $action . '.title');
//                                $message = config('schedulenotifications.scheduletask.' . $action . '.message');
//                                if (isset($title) && isset($message) && !empty($title) && !empty($message)) {
//                                    $notifyTitle = notificationStringReplacement($title, $replaceString);
//                                    $notifyMessage = notificationStringReplacement($message, $replaceString);
//                                    $notificaion = array("fk_company_id" => $companyID, "notification_id" => $scheduleDetails->schedule_reference_id, "notification_title" => trim($notifyTitle), "notification_message" => trim($notifyMessage), "notification_reference_id" => $taskUserID, "notification_type" => config('2iequipstatus.notificationtype.schedule'), "parent_id" => $value->parent_id, "user_type" => $value->user_type, 'foruser' => $value->usrID, "fk_users_id" => $value->usrID, "created_by" => $value->usrID, "updated_by" => $value->usrID, "created_on" => createddatetime(), "updated_on" => createddatetime(), "status" => 1, "dels" => 0, 'userdata' => $userdata);
//                                    if (isset($value->emailid)) {
//                                        $groupusermail[] = $value->emailid;
//                                    }
//                                    CustomDbModel::NotifictionAndPush($notificaion);
//                                }
//                            }
//                        }
//
//                        if (count($groupusermail) > 0) {
//                            $notificationpreference = DB::table('company')->where('id', $notificaion['fk_company_id'])->select(array('push_notification_preference', 'email_preference'))->first();
//
//                            if ($notificationpreference->email_preference == 1) {
//                                $data = array(
//                                    "url" => str_replace('api/public', '', url('/')) . '/',
//                                    "notificationtitle" => $notificaion['notification_title'],
//                                    "notificationmessage" => $notificaion['notification_message']
//                                );
//                                $email = $groupusermail;
//                                $subject = $notificaion['notification_title'];
//                                Mail::send('emails.notification', $data, function ($message) use ($email, $subject) {
//                                    $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//                                    $message->to($email)->subject($subject);
//                                });
//                            }
//                        }
//                    }
//                }
//            }
//        } catch (\Exception $ex) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/TicketNotification-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function sendnotification($notificaion) {
//        if (isset($notificaion['notification_id']) && isset($notificaion['notification_title']) && !empty($notificaion['notification_title']) && isset($notificaion['notification_message']) && !empty($notificaion['notification_message']) && isset($notificaion['notification_reference_id']) && isset($notificaion['notification_type']) && isset($notificaion['parent_id']) && !empty($notificaion['parent_id']) && isset($notificaion['user_type']) && isset($notificaion['foruser'])) {
//            try {
//                if (isset($notificaion['fk_company_id'])) {
//                    $notificaion['fk_company_id'] = $notificaion['fk_company_id'];
//                } elseif (CustomDbModel::getClientID() != 0) {
//                    $notificaion['fk_company_id'] = CustomDbModel::getClientID();
//                }
//
//                $groupusermail = array();
//                $notificaion['fk_users_id'] = $notificaion['foruser'];
//                $notificaion['created_by'] = $notificaion['foruser'];
//                $notificaion['updated_by'] = $notificaion['foruser'];
//                $notificaion['dels'] = 0;
//                $notificaion['status'] = 1;
//                $notificaion['notification_title'] = trim($notificaion['notification_title']);
//                $notificaion['notification_message'] = trim($notificaion['notification_message']);
//                $notificaion['created_on'] = createddatetime();
//                $notificaion['updated_on'] = createddatetime();
//
//                if (!isset($notificaion['autotrigger']) && $notificaion['foruser'] == 0) {
//
//                    if ($notificaion['user_type'] == config('2iequipstatus.usertype.customer')) {
//                        $companydetaisget = DB::table('company')->where('id', $notificaion['fk_company_id'])->select(array('fk_business_types_id'))->first();
//                        if (isset($companydetaisget->fk_business_types_id) && $companydetaisget->fk_business_types_id == 2) {
//                            $getuserdetails = DB::table('customers_users_mapping')->leftJoin('users', 'users.id', '=', 'customers_users_mapping.fk_users_id')->where('customers_users_mapping.fk_company_id', $notificaion['fk_company_id'])->where('customers_users_mapping.fk_customers_id', $notificaion['parent_id'])->where('customers_users_mapping.status', 1)->where('customers_users_mapping.dels', 0)->where('users.status', 1)->where('users.dels', 0)->where('users.user_type', config('2iequipstatus.usertype.customer'))->select(array('customers_users_mapping.fk_users_id', 'users.emailid'))->get();
//                            foreach ($getuserdetails as $key => $value) {
//                                $notificaion['foruser'] = $value->fk_users_id;
//                                array_push($groupusermail, $value->emailid);
//                                CustomDbModel::NotifictionAndPush($notificaion);
//                            }
//                        } elseif (isset($companydetaisget->fk_business_types_id) && $companydetaisget->fk_business_types_id == 3) {
//                            if ($notificaion['notification_type'] == config('2iequipstatus.notificationtype.ticket')) {
//                                $gettraceability = DB::table('ticket')->where('fk_company_id', $notificaion['fk_company_id'])->where('fk_customers_id', $notificaion['parent_id'])->where('id', $notificaion['notification_reference_id'])->select(array('fk_equipment_traceability_id'))->first();
//                            }
//
//                            if (isset($gettraceability->fk_equipment_traceability_id)) {
//                                $getuserdetails = DB::table('customers_locations_serialnumber_mapping')->where('customers_locations_serialnumber_mapping.fk_company_id', $notificaion['fk_company_id'])->where('customers_locations_serialnumber_mapping.fk_customers_id', $notificaion['parent_id'])->where('customers_locations_serialnumber_mapping.fk_equipment_traceability_id', $gettraceability->fk_equipment_traceability_id)->leftJoin('customers_locations_users_mapping', 'customers_locations_users_mapping.fk_customers_locations_id', '=', 'customers_locations_serialnumber_mapping.fk_customers_locations_id')->leftJoin('users', 'users.id', '=', 'customers_locations_users_mapping.fk_users_id')->where('users.status', 1)->where('users.dels', 0)->where('customers_locations_serialnumber_mapping.status', 1)->where('customers_locations_serialnumber_mapping.dels', 0)->where('customers_locations_users_mapping.status', 1)->where('customers_locations_users_mapping.dels', 0)->where('users.user_type', config('2iequipstatus.usertype.loactionhead'))->select(array('users.id', 'users.emailid'))->get();
//                                if (!empty($getuserdetails)) {
//                                    foreach ($getuserdetails as $key => $value) {
//                                        $notificaion['user_type'] = config('2iequipstatus.usertype.loactionhead');
//                                        $notificaion['foruser'] = $value->id;
//                                        array_push($groupusermail, $value->emailid);
//                                        CustomDbModel::NotifictionAndPush($notificaion);
//                                    }
//                                }
//                                $getuserformachineowner = DB::table('serial_number_user_mapping')->where('fk_company_id', $notificaion['fk_company_id'])->where('fk_equipment_traceability_id', $gettraceability->fk_equipment_traceability_id)->where('status', 1)->where('dels', 0)->select(array('fk_users_id'))->first();
//                                if (isset($getuserformachineowner->fk_users_id)) {
//                                    $notificaion['user_type'] = config('2iequipstatus.usertype.machineowner');
//                                    $notificaion['foruser'] = $getuserformachineowner->fk_users_id;
//                                    CustomDbModel::NotifictionAndPush($notificaion);
//                                }
//                            }
//                        }
//                    } elseif ($notificaion['user_type'] == config('2iequipstatus.usertype.serviceagency')) {
//                        $companydetaisget = DB::table('company')->where('id', $notificaion['fk_company_id'])->select(array('fk_business_types_id'))->first();
//
//                        if (isset($companydetaisget->fk_business_types_id) && $companydetaisget->fk_business_types_id == 2) {
//                            $getuserdetails = DB::table('serviceagency_users_mapping')->leftJoin('users', 'users.id', '=', 'serviceagency_users_mapping.fk_users_id')->where('serviceagency_users_mapping.fk_company_id', $notificaion['fk_company_id'])->where('serviceagency_users_mapping.fk_serviceagency_id', $notificaion['parent_id'])->where('serviceagency_users_mapping.status', 1)->where('serviceagency_users_mapping.dels', 0)->where('users.status', 1)->where('users.dels', 0)->where('users.user_type', config('2iequipstatus.usertype.serviceagency'))->select(array('serviceagency_users_mapping.fk_users_id', 'users.emailid'))->get();
//                            foreach ($getuserdetails as $key => $value) {
//                                $notificaion['foruser'] = $value->fk_users_id;
//                                array_push($groupusermail, $value->emailid);
//                                CustomDbModel::NotifictionAndPush($notificaion);
//                            }
//                        }
//                    } else {
//                        CustomDbModel::NotifictionAndPush($notificaion);
//                    }
//                } else {
//                    CustomDbModel::NotifictionAndPush($notificaion);
//                }
//
//                if (count($groupusermail) > 0) {
//                    $notificationpreference = DB::table('company')->where('id', $notificaion['fk_company_id'])->select(array('push_notification_preference', 'email_preference'))->first();
//
//                    if ($notificationpreference->email_preference == 1) {
//                        $data = array(
//                            "url" => str_replace('api/public', '', url('/')) . '/',
//                            "notificationtitle" => $notificaion['notification_title'],
//                            "notificationmessage" => $notificaion['notification_message']
//                        );
//                        $email = $groupusermail;
//                        $subject = $notificaion['notification_title'];
//                        Mail::send('emails.notification', $data, function ($message) use ($email, $subject) {
//                            $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//                            $message->to($email)->subject($subject);
//                        });
//                    }
//                }
//                return true;
//            } catch (\Exception $exc) {
//                $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $exc . "\r\n" . '------------' . PHP_EOL;
//                file_put_contents(env("APP_LOG_PATH") . "/Notification_formation-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//            }
//        }
//    }
//
//    public static function addticktoqueue($userdata, $equipmentmodel, $ticketstatus, $ticketid, $working_in_problem) {
//
//        $slaassetmapping = DB::table('sla_asset_mapping')->select(array('sla.id', 'sla_asset_mapping.sla_manufacturer', 'sla_asset_mapping.sla_categories', 'sla_asset_mapping.sla_sub_categories', 'sla_asset_mapping.sla_equipments', 'sla_asset_mapping.sla_equipment_models', 'sla_rules.fk_esclation_group_id', 'sla_rules.sla_rules_for', 'sla_rules.esclation_duration', 'sla_rules.esclation_interval'))->leftjoin('sla', 'sla.id', '=', 'sla_asset_mapping.fk_sla_id')->leftjoin('sla_rules', 'sla.id', '=', 'sla_rules.fk_sla_id')->leftjoin('esclation_group', 'esclation_group.id', '=', 'sla_rules.fk_esclation_group_id')->where('sla_rules.sla_rules_for', $ticketstatus)->where('sla.status', 1)->where('sla_rules.fk_company_id', $userdata->usermaping->company)->where('sla.dels', 0)->where('esclation_group.esclation_for', config('2iequipstatus.reminderandescalationtype.Tickets'))->where('esclation_group.status', 1)->where('esclation_group.dels', 0)->where('sla_rules.working_condition', $working_in_problem)->get();
//
//        foreach ($slaassetmapping as $key => $value) {
//            if ($value->sla_equipment_models != '') {
//                $equipmentmodelarray = explode(',', $value->sla_equipment_models);
//                if (in_array($equipmentmodel, $equipmentmodelarray)) {
//                    switch ($value->esclation_interval) {
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.minutes')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addMinutes(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.hours')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addHours(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.days')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addDays(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.weeks')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addWeeks(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.months')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addMonths(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.years')): {
//                                $job = (new TicketSLA($userdata, $value, $ticketid, array()))->delay(Carbon::now()->addYears(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                    }
//
//                    $esclation = DB::table('reminders_esclations')->select(array('notify_type', 'frequency_duration', 'frequency_interval', 'notify_message', 'notify_roles', 'notify_users', 'is_notify_emails', 'is_notify_roles'))->where('notify_type', config('2iequipstatus.escalation_notify_type.escalation'))->where('status', 1)->where('dels', 0)->where('fk_company_id', $userdata->usermaping->company)->get();
//                    if (count($esclation) > 0) {
//                        foreach ($esclation as $esclationk => $esclationv) {
//                            switch ($esclationv->frequency_interval) {
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.minutes')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addMinutes(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.hours')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addHours(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.days')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addDays(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.weeks')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addWeeks(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.months')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addMonths(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.years')): {
//                                        $job = (new TicketSLA($userdata, $value, $ticketid, $esclationv))->delay(Carbon::now()->addYears(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                            }
//                        }
//                    }
//                }
//            }
//        }
//        return true;
//    }
//
//    public static function addserviceEngineertickettoqueue($userdata, $equipmentmodel, $ticketstatus, $ticketid, $working_in_problem, $engineerID, $EnggHistoryID) {
//        $slaassetmapping = DB::table('sla_asset_mapping')->select(array('sla.id', 'sla_asset_mapping.sla_manufacturer', 'sla_asset_mapping.sla_categories', 'sla_asset_mapping.sla_sub_categories', 'sla_asset_mapping.sla_equipments', 'sla_asset_mapping.sla_equipment_models', 'sla_rules.fk_esclation_group_id', 'sla_rules.sla_rules_for', 'sla_rules.esclation_duration', 'sla_rules.esclation_interval'))->leftjoin('sla', 'sla.id', '=', 'sla_asset_mapping.fk_sla_id')->leftjoin('sla_rules', 'sla.id', '=', 'sla_rules.fk_sla_id')->leftjoin('esclation_group', 'esclation_group.id', '=', 'sla_rules.fk_esclation_group_id')->where('sla_rules.sla_rules_for', $ticketstatus)->where('sla_rules.fk_company_id', $userdata->usermaping->company)->where('sla.status', 1)->where('sla.dels', 0)->where('esclation_group.esclation_for', config('2iequipstatus.reminderandescalationtype.Tickets'))->where('esclation_group.status', 1)->where('esclation_group.dels', 0)->where('sla_rules.working_condition', $working_in_problem)->get();
//
//        foreach ($slaassetmapping as $key => $value) {
//            if ($value->sla_equipment_models != '') {
//                $equipmentmodelarray = explode(',', $value->sla_equipment_models);
//                if (in_array($equipmentmodel, $equipmentmodelarray)) {
//                    switch ($value->esclation_interval) {
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.minutes')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addMinutes(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.hours')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addHours(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.days')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addDays(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.weeks')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addWeeks(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.months')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addMonths(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                        case ($value->esclation_interval == config('2iequipstatus.ticketsla.years')): {
//                                $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, array(), $engineerID, $EnggHistoryID))->delay(Carbon::now()->addYears(round($value->esclation_duration)));
//                                dispatch($job);
//                                break;
//                            };
//                    }
//
//                    $esclation = DB::table('reminders_esclations')->select(array('notify_type', 'frequency_duration', 'frequency_interval', 'notify_message', 'notify_roles', 'notify_users'))->where('notify_type', config('2iequipstatus.escalation_notify_type.escalation'))->where('fk_company_id', $userdata->usermaping->company)->where('status', 1)->where('dels', 0)->get();
//                    if (count($esclation) > 0) {
//                        foreach ($esclation as $esclationk => $esclationv) {
//                            switch ($esclationv->frequency_interval) {
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.minutes')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addMinutes(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.hours')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addHours(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.days')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addDays(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.weeks')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addWeeks(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.months')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addMonths(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                                case ($esclationv->frequency_interval == config('2iequipstatus.ticketsla.years')): {
//                                        $job = (new TicketServiceEngineerSLA($userdata, $value, $ticketid, $esclationv, $engineerID, $EnggHistoryID))->delay(Carbon::now()->addYears(round($esclationv->frequency_duration)));
//                                        dispatch($job);
//                                        break;
//                                    };
//                            }
//                        }
//                    }
//                }
//            }
//        }
//    }
//
////    public static function CreateNotifiactionLog($logtype, $message, $userdata, $device = '', $pushtoken = '', $deliverystatus = '') {
////        if ($logtype == 1) {
////            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . json_encode($message) . "\r\n" . 'App User_Id: ' . $userdata->id . "\r\n" . 'User Email: ' . $userdata->emailid . "\r\n" . 'Device: ' . $device . "\r\n" . 'PushToken: ' . $pushtoken . "\r\n" . 'Device Name: ' . $deliverystatus . "\r\n" . '------------' . PHP_EOL;
////            file_put_contents(env("APP_LOG_PATH") . "/push_Notification_SuccessLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
////        } else {
////            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $message . "\r\n" . 'App User_Id: ' . $userdata->id . "\r\n" . 'User Email: ' . $userdata->emailid . "\r\n" . 'Device: ' . $device . "\r\n" . 'PushToken: ' . $pushtoken . "\r\n" . 'Device Name: ' . $deliverystatus . "\r\n" . '------------' . PHP_EOL;
////            file_put_contents(env("APP_LOG_PATH") . "/push_Notification_ErrorLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
////        }
////    }
//
//    public static function assetsCountHandler($companyid, $addasset = false, $assetstatus = false, $deleteasset = false) {
//        try {
//            $getcompanydetails = DB::table('company')->where('id', $companyid)->select(array('asset_restriction', 'fk_business_types_id'))->first();
//            $getcompanyusage = DB::table('company_usage')->where('fk_company_id', $companyid)->select(array('id', 'equipments_limit', 'equipments', 'active_equipments', 'inactive_equipments'))->first();
//            if ($getcompanydetails->asset_restriction == 1 && $addasset == true) {
//                if ($getcompanydetails->fk_business_types_id == 2) {
//                    $totalassetparams = array(
//                        "selectcol" => array('id'),
//                        "condition" => array('customers_asset_mapping.dels' => 0, 'customers_equipment_mapping.dels' => 0, 'equipment_traceability.dels' => 0, 'customers_equipment_mapping.fk_company_id' => $companyid),
//                        "leftjoin" => array(array('customers_asset_mapping', 'customers_asset_mapping.id', '=', 'customers_equipment_mapping.fk_customers_asset_mapping_id'), array('equipment_traceability', 'equipment_traceability.id', '=', 'customers_equipment_mapping.fk_equipment_traceability_id')),
//                        'retrunrowcount' => true
//                    );
//                    $assetcont = CustomDbModel::rowCheckquery('customers_equipment_mapping', $totalassetparams, false);
//                } else {
//                    $totalassetparams = array(
//                        "selectcol" => array('fk_equipment_traceability_id'),
//                        "condition" => array('equipment_traceability.dels' => 0, 'customers_locations_serialnumber_mapping.dels' => 0, 'customers_locations_serialnumber_mapping.fk_company_id' => $companyid),
//                        "leftjoin" => array(array('equipment_traceability', 'equipment_traceability.id', '=', 'customers_locations_serialnumber_mapping.fk_equipment_traceability_id')),
//                        "retrunrowcount" => true
//                    );
//                    $assetcont = CustomDbModel::rowCheckquery('customers_locations_serialnumber_mapping', $totalassetparams, false);
//                }
//
//                if ($getcompanyusage->equipments_limit < $assetcont) {
//                    return false;
//                } else {
//                    return true;
//                }
//            } elseif ($getcompanydetails->asset_restriction == 0 && $addasset == true) {
//                return true;
//            }
//            return false;
//        } catch (\Exception $exc) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $exc . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/Plan_subscription_ErrorLog-BD-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function userCountHandler($companyid, $userdata, $useradd = false) {
//        try {
//            $getcompanydetails = DB::table('company')->where('id', $companyid)->select(array('user_restriction', 'serviceagency_restriction', 'serviceengineer_restriction', 'fk_business_types_id'))->first();
//            $getcompanyusage = DB::table('company_usage')->where('fk_company_id', $companyid)->select(array('id', 'users_limit', 'totalusers', 'active_totalusers', 'inactive_totalusers'))->first();
//
//            $usertype = array();
//            if ($getcompanydetails->fk_business_types_id == 2) {
//                $usertype = array(config('2iequipstatus.usertype.appuser'), config('2iequipstatus.usertype.customer'));
//            }
//            if ($getcompanydetails->user_restriction == 1 && $useradd == true) {
//                $usercont = CustomDbModel::rowCheckquery('users', array('retrunrowcount' => true, 'select' => array('id'), 'condition' => array('dels' => 0, 'fk_company_id' => $companyid), 'where_not_in' => array('user_type' => $usertype)), false);
//
//                if ($getcompanyusage->users_limit < $usercont) {
//                    return false;
//                } else {
//                    return true;
//                }
//            } elseif ($getcompanydetails->user_restriction == 0 && $useradd == true) {
//                return true;
//            }
//
//            return false;
//        } catch (\Exception $exc) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $exc . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/Plan_subscription_ErrorLog-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function serviceagencyuserCountHandler($company, $servicagency, $useradd = false, $statusupdate = false, $userid = 0, $serviceagencycount = 0, $usertype = 'appuser') {
//        try {
//
//            $getserviceagencydetails = DB::table('serviceagency_company_mapping')->where('fk_company_id', $company)->where('fk_serviceagency_id', $servicagency)->select(array('id', 'company_requested_users', 'company_user_usage'))->first();
//
//            if ($getserviceagencydetails->company_requested_users > $getserviceagencydetails->company_user_usage && $useradd == true && $statusupdate == false && $userid == 0) {
//                if ($serviceagencycount == 0) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage + 1));
//                    return true;
//                } elseif ($serviceagencycount > 0 && $getserviceagencydetails->company_requested_users >= $getserviceagencydetails->company_user_usage + $serviceagencycount) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage + $serviceagencycount));
//                    return true;
//                }
//                return false;
//            } elseif ($getserviceagencydetails->company_requested_users > $getserviceagencydetails->company_user_usage && $useradd == false && $statusupdate == true && $userid == 0) {
//                if ($serviceagencycount == 0) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage + 1));
//                    return true;
//                } elseif ($serviceagencycount > 0 && $getserviceagencydetails->company_requested_users >= $getserviceagencydetails->company_user_usage + $serviceagencycount) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage + $serviceagencycount));
//                    return true;
//                }
//                return false;
//            } elseif ($useradd == false && $statusupdate == false && $userid == 0) {
//                if ($serviceagencycount == 0) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage - 1));
//                    return true;
//                } elseif ($serviceagencycount > 0) {
//                    DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage - $serviceagencycount));
//                    return true;
//                }
//                return false;
//            } elseif ($getserviceagencydetails->company_requested_users > $getserviceagencydetails->company_user_usage && $useradd == false && $statusupdate == false && $userid != 0) {
//                $getserviceagnecy = DB::table('serviceagency_company_mapping')->where('fk_company_id', $userid)->where('fk_serviceagency_id', $servicagency)->select(array('id', 'company_requested_users', 'company_user_usage'))->first();
//                DB::table('serviceagency_company_mapping')->where('id', $getserviceagnecy->id)->update(array('company_user_usage' => $getserviceagnecy->company_user_usage - 1));
//                DB::table('serviceagency_company_mapping')->where('id', $getserviceagencydetails->id)->update(array('company_user_usage' => $getserviceagencydetails->company_user_usage + 1));
//
//                return true;
//            }
//
//            return false;
//        } catch (\Exception $exc) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $exc . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/Plan_subscription_ErrorLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function customeruserCountHandler($serviceagency, $customer, $useradd = false, $statusupdate = false, $userid = 0) {
//        try {
//            $getserviceagencydetails = DB::table('customers')->where('fk_serviceagency_id', $serviceagency)->where('id', $customer)->select(array('id', 'serviceagency_requested_users', 'serviceagency_user_usage'))->first();
//
//            if ($getserviceagencydetails->serviceagency_requested_users > $getserviceagencydetails->serviceagency_user_usage && $useradd == true && $statusupdate == false && $userid == 0) {
//                DB::table('customers')->where('id', $getserviceagencydetails->id)->update(array('serviceagency_user_usage' => $getserviceagencydetails->serviceagency_user_usage + 1));
//                return true;
//            } elseif ($getserviceagencydetails->serviceagency_requested_users > $getserviceagencydetails->serviceagency_user_usage && $useradd == false && $statusupdate == true && $userid == 0) {
//                DB::table('customers')->where('id', $getserviceagencydetails->id)->update(array('serviceagency_user_usage' => $getserviceagencydetails->serviceagency_user_usage + 1));
//                return true;
//            } elseif ($useradd == false && $statusupdate == false && $userid == 0) {
//                DB::table('customers')->where('id', $getserviceagencydetails->id)->update(array('serviceagency_user_usage' => $getserviceagencydetails->serviceagency_user_usage - 1));
//                return true;
//            } elseif ($getserviceagencydetails->serviceagency_requested_users > $getserviceagencydetails->serviceagency_user_usage && $useradd == false && $statusupdate == false && $userid != 0) {
//                $getserviceagnecy = DB::table('customers')->where('fk_customers_id', $customer)->where('fk_serviceagency_id', $userid)->select(array('id', 'serviceagency_requested_users', 'serviceagency_user_usage'))->first();
//                DB::table('customers')->where('id', $getserviceagnecy->id)->update(array('serviceagency_user_usage' => $getserviceagnecy->serviceagency_user_usage - 1));
//                DB::table('customers')->where('id', $getserviceagencydetails->id)->update(array('serviceagency_user_usage' => $getserviceagencydetails->serviceagency_user_usage + 1));
//                return true;
//            }
//            return false;
//        } catch (\Exception $exc) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $exc . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/Plan_subscription_ErrorLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function serviceagencyusercustomerhandling($company, $servicagency, $customer, $serviceagencycount, $useradd = false, $statusupdate = false) {
//        try {
//            if ($useradd == true && $statusupdate == false) {
//                foreach ($company as $key => $value) {
//                    $checkcompany = DB::table('company_customer_user_allotment')->where('fk_company_id', $value)->where('fk_serviceagency_id', $servicagency)->where('fk_customers_id', $customer)->select(array('id', 'users_allotment', 'user_used'))->first();
//                    $getserviceagency = DB::table('serviceagency_company_mapping')->where('fk_company_id', $value)->where('fk_serviceagency_id', $servicagency)->select(array('id', 'company_requested_users', 'company_user_usage'))->first();
//                    if (isset($checkcompany->users_allotment) && isset($checkcompany->user_used) && $checkcompany->user_used < $serviceagencycount[$value]) {
//
//                        if ($checkcompany->user_used < $serviceagencycount[$value]) {
//                            
//                        } elseif ($checkcompany->user_used > $serviceagencycount[$value] && $getserviceagency->company_requested_users > $serviceagencycount[$value]) {
//                            DB::table('serviceagency_company_mapping')->where('id', $getserviceagency->id)->update(array('company_user_usage' => $serviceagencycount[$value]));
//                            DB::table('company_customer_user_allotment')->where('fk_company_id', $value)->where('fk_serviceagency_id', $servicagency)->where('fk_customers_id', $customer)->update(array('users_allotment' => $serviceagencycount[$value]));
//                        }
//                    } elseif (!isset($checkcompany->users_allotment) && !isset($checkcompany->user_used)) {
//                        if ($getserviceagency->company_requested_users > ($getserviceagency->company_user_usage + $serviceagencycount[$value])) {
//                            DB::table('serviceagency_company_mapping')->where('id', $getserviceagency->id)->update(array('company_user_usage' => $getserviceagency->company_user_usage + $serviceagencycount[$value]));
//                            DB::table('company_customer_user_allotment')->insertGetId(array('fk_company_id' => $value, 'fk_serviceagency_id' => $servicagency, 'fk_customers_id' => $customer, 'users_allotment' => $serviceagencycount[$value], 'created_by' => $servicagency, 'updated_by' => $servicagency, 'created_on' => createddatetime()));
//                        } else {
//                            return false;
//                        }
//                    }
//                }
//                return true;
//            }
//            return false;
//        } catch (\Exception $ex) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/Plan_subscription_ErrorLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public function auditLog($oldvalue, $newValue, $table_name) {
//        $oldData = json_decode($oldvalue, true);
//        $newData = json_decode($newValue, true);
//        $log_msg = "";
//        foreach ($oldData[0] as $key => $val) {
//            if (isset($newData[$key]) && $oldData[0][$key] != $newData[$key]) {
//                if ($oldData[0][$key] == '') {
//                    $oldData[0][$key] = "null";
//                }
//                $log_msg.=$key . ' changed from ' . $oldData[0][$key] . ' to ' . $newData[$key] . "###";
//            }
//        }
//        $authorizer = app('oauth2-server.authorizer');
//        $authorizer->validateAccessToken();
//        $getauthusderid = $authorizer->getResourceOwnerId();
//        $mappedData = $this->getsessionuser($getauthusderid);
//        $email_id = $mappedData->emailid;
//        $user_id = $getauthusderid;
//        $client_ip = \Request::getClientIp(true);
//        $action = "U";
//        if ($log_msg != '') {
//            DB::table('audit_log')->insert(
//                    array(
//                        'client_ip' => $client_ip,
//                        'email_id' => $email_id,
//                        'user_id' => $user_id,
//                        'action' => $action,
//                        'table_name' => $table_name,
//                        'log_msg' => $log_msg,
//            ));
//        }
//    }
//
//    public static function languagejson($fk_company_id, $languagekey, $userid = null) {
//
//
//
//        $query_param = array('fk_lang_id' => $languagekey, 'fk_company_id' => $fk_company_id);
//        $uniquecheck = CustomDbModel::rowCheckquery('language_client', array('condition' => $query_param), false);
//
//        if (!$uniquecheck) {
//            $language = DB::select("SELECT label_key, (CASE WHEN label_value is NULL THEN lang_label_value else label_value END) as lang_label_value 
// FROM ASSIST_DEFAULT_LABEL_CONFIG_$languagekey as dflt left join( select fk_label_id label_id, lang_label_value label_value from assist_language_client lang_cli, assist_language_label_config lang_lbl_config where lang_cli.id = lang_lbl_config.fk_lang_client_id and fk_company_id is null  and fk_lang_id = '" . $languagekey . "') as cfg on dflt.label_id = cfg.label_id;");
//        } else {
//            $language = DB::select("SELECT label_key, (CASE WHEN label_value is NULL THEN lang_label_value else label_value END) as lang_label_value 
//         FROM ASSIST_DEFAULT_LABEL_CONFIG_$languagekey as dflt left join( select fk_label_id label_id, lang_label_value label_value from assist_language_client lang_cli, assist_language_label_config lang_lbl_config where lang_cli.id = lang_lbl_config.fk_lang_client_id and fk_company_id =$fk_company_id  and fk_lang_id = '" . $languagekey . "') as cfg on dflt.label_id = cfg.label_id;");
//        }
//
//        $languagearray = array();
//        foreach ($language as $key => $value) {
//            $languagearray[$value->label_key] = $value->lang_label_value;
//        }
//
//        if ($userid) {
//            Cache::forever($userid, $languagearray);
//        }
//
//        return $languagearray;
//    }
//
//    public static function rawQuery($query, $input, $tempTable) {
//        $queryObj = DB::select($query);
//        $griddataobj = DB::table($tempTable);
//        if (isset($input['filter']))
//            foreach ($input['filter'] as $key => $value) {
//                if (isset($value) && !empty($value)) {
//                    $impoldvalue = explode('_', $key);
//                    $filterArray = array();
//                    if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date') {
//                        $griddataobj->where($key, "LIKE", "%" . datetodb($value) . "%");
//                    } else {
//                        if (is_array($value)) {
//                            foreach ($value as $val) {
//                                if (is_int($val)) {
//                                    $filterArray[] = $val;
//                                }
//                            }
//                            if (!empty($filterArray)) {
//                                $griddataobj->whereIn($key, $value);
//                            }
//                        } else if (is_int($value)) {
//                            $griddataobj->where($key, $value);
//                        } else {
//                            $key = '"' . str_replace(".", '"."', $key) . '"';
//                            $griddataobj->whereRaw(" LOWER(" . $key . ") Like LOWER( '%" . $value . "%' )");
//                        }
//                    }
//                }
//            }
//        $griddata['params']['total'] = $griddataobj->get()->count();
//
//        if (isset($input['count']))
//            $griddataobj->limit($input['count']);
//
//        if (isset($input['page'])) {
//            $gridpage = $input['count'] * ($input['page'] - 1);
//            $griddataobj->offset($gridpage);
//        }
//
//        if (isset($input['sorting'])) {
//            foreach ($input['sorting'] as $key => $value)
//                $griddataobj->orderBy($key, $value);
//        }
//
//        $griddata['list'] = $griddataobj->get();
//
//        foreach ($griddata['list'] as $key => $value) {
//            $griddata['list'][$key]->sno = (isset($gridpage) ? $gridpage * 1 : 0 ) + ($key + 1);
//            if (isset($value->id)) {
//                $griddata['list'][$key]->id = Crypt::encrypt($value->id);
//            }
//
//            if (isset($griddata['list'][$key]->status))
//                $griddata['list'][$key]->status = ($value->status) ? TRUE : FALSE;
//
//            foreach ($value as $k => $v) {
//                $impoldvalue = explode('_', $k);
//                if (isset($impoldvalue[1]) && $impoldvalue[1] == 'date')
//                    if (isset($v) && $v != '')
//                        $griddata['list'][$key]->$k = datetoapi($v);
//                if (isset($impoldvalue[1]) && $impoldvalue[1] == 'datetime')
//                    if (isset($v) && $v != '')
//                        $griddata['list'][$key]->$k = datetimetoapi($v);
//            }
//        }
//        $griddataobj = DB::raw("DROP TABLE assist_$tempTable");
//        return $griddata;
//    }
//
//    public static function perfixsuffix($module, $fk_company_id, $id) {
//        $perfixsuffixdetails = DB::table('company_entity_prefix')->where('fk_company_id', $fk_company_id)->where('module', $module)->select(array('prefix', 'starting_from', 'current_value', 'suffix', 'counter'))->first();
//        if (isset($perfixsuffixdetails->current_value) && isset($perfixsuffixdetails->starting_from)) {
//            if ($perfixsuffixdetails->current_value == 0) {
//                $startvalue = $perfixsuffixdetails->starting_from;
//            } elseif ($perfixsuffixdetails->current_value < $perfixsuffixdetails->starting_from) {
//                $startvalue = $perfixsuffixdetails->starting_from;
//            } else {
//                $startvalue = $perfixsuffixdetails->current_value + 1;
//            }
//
//            if (strtolower($perfixsuffixdetails->suffix) == 'year') {
//                $suffix = date('Y');
//            } else {
//                $suffix = ($perfixsuffixdetails->suffix != '') ? $perfixsuffixdetails->suffix : '';
//            }
//            DB::table('company_entity_prefix')->where('fk_company_id', $fk_company_id)->where('module', $module)->update(array('current_value' => $startvalue));
//            return $perfixsuffixdetails->prefix . str_pad($startvalue, $perfixsuffixdetails->counter, '0', STR_PAD_LEFT) . $suffix;
//        } else {
//            if ($module == config('2iequipstatus.company_entity_prefix_module.schedule')) {
//                return 'SCH' . str_pad($id, 4, '0', STR_PAD_LEFT);
//            } elseif ($module == config('2iequipstatus.company_entity_prefix_module.ticket')) {
//                return 'TKT' . str_pad($id, 4, '0', STR_PAD_LEFT);
//            } elseif ($module == config('2iequipstatus.company_entity_prefix_module.spareparts_schedule')) {
//                return 'SPSC' . str_pad($id, 4, '0', STR_PAD_LEFT);
//            }
//        }
//    }
//
//    public static function getTicketdetails($id) {
//        $ticketdetails = DB::table('ticket')->where('id', $id)->select(array('fk_equipment_traceability_id', 'fk_company_id', 'fk_customers_id', 'fk_customers_locations_id', 'ticket_id', 'ticket_type', 'priority', 'working_in_problem', 'raised_on', 'created_by'))->first();
//        if (isset($ticketdetails->fk_equipment_traceability_id)) {
//            $ticketdetails->assetdetails = DB::table('equipment_traceability')->where('id', $ticketdetails->fk_equipment_traceability_id)->leftJoin('equipments_model', 'equipments_model.id', '=', 'equipment_traceability.fk_equipments_model_id')->leftJoin('equipments', 'equipments.id', '=', 'equipments_model.fk_equipments_id')->select(array())->first();
//        }
//    }
//
//    public static function NotifictionAndPush($notificationdata) {
//        DB::beginTransaction();
//        try {
//            if (isset($notificationdata['fk_company_id']) && !empty($notificationdata['fk_company_id']) && isset($notificationdata['notification_title']) && !empty($notificationdata['notification_title']) && isset($notificationdata['notification_message']) && !empty($notificationdata['notification_message']) && isset($notificationdata['notification_reference_id']) && isset($notificationdata['notification_type']) && isset($notificationdata['parent_id']) && !empty($notificationdata['parent_id']) && isset($notificationdata['user_type']) && isset($notificationdata['foruser'])) {
//                $autotrigger = false;
//                if ($notificationdata['foruser'] != 0) {
//                    $userdata = DB::table('users')->where('id', $notificationdata['foruser'])->select(array('id', 'emailid', 'user_type'))->first();
//                    if (isset($userdata->user_type)) {
//                        $notificationdata['user_type'] = $userdata->user_type;
//                    }
//                }
//                if (isset($notificationdata['autotrigger'])) {
//                    $autotrigger = $notificationdata['autotrigger'];
//                    unset($notificationdata['autotrigger']);
//                }
//                unset($notificationdata['userdata']);
//                $query = DB::table('notification')->insertGetId($notificationdata);
//
//                $notificationpreference = DB::table('company')->where('id', $notificationdata['fk_company_id'])->select(array('push_notification_preference', 'email_preference'))->first();
////
////                if ($notificationpreference->email_preference == 1 && $autotrigger == false) {
////                    $data = array(
////                        "url" => str_replace('api/public', '', url('/')) . '/',
////                        "notificationtitle" => $notificationdata['notification_title'],
////                        "notificationmessage" => $notificationdata['notification_message']
////                    );
////                    $email = $userdata->emailid;
////                    $subject = $notificationdata['notification_title'];
////                    Mail::send('emails.notification', $data, function ($message) use ($email, $subject) {
////                        $message->from('noreply@innothinkglobal.com', 'InnoAssist');
////                        $message->to($email)->subject($subject);
////                    });
////                }
//
//                if ($query) {
//                    if (!isset($userdata->id)) {
//                        $userdata = (object) array('id' => $notificationdata['fk_users_id']);
//                    } else {
//                        $appuserid = DB::table('user_mobile_tokens')->where('fk_users_id', $userdata->id)->select(array('id', 'device_id', 'device_type', 'push_token', 'notification_status', 'fk_users_id'))->first();
//                        if (isset($appuserid->id) && isset($appuserid->push_token) && !empty($appuserid->push_token) && isset($appuserid->notification_status) && $appuserid->notification_status == 1 && $notificationpreference->push_notification_preference == 1) {
//                            if (strpos($appuserid->device_id, '-')) {
//                                try {
//                                    $ch = curl_init("https://fcm.googleapis.com/fcm/send");
//
//                                    $arrayToSend = array('to' => $appuserid->push_token, 'notification' => array('title' => $notificationdata['notification_title'], 'text' => $notificationdata['notification_message']), 'priority' => 'high');
//                                    $headers = array('Authorization: key=AAAAlxnrk3w:APA91bH-uaGguz9qntsv092Q4v5odPvWlm6GJnOtuPgQ9kBNbXZxyxsMVj4HIKzEjW7RQhoDplmikDZuECh09Xb8SRyz5u7VowirS3LhOCoi3NNZBWdNDPNp5xpK_GDttM3_jBT5duW8');
//                                    $headers[] = 'Content-Type: application/json';
//                                    $headers[] = 'Authorization: key= $key';
//                                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//                                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
//                                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//                                    $result = curl_exec($ch);
//
//                                    if ($result) {
//                                        CustomDbModel::CreateNotifiactionLog(1, $result, $userdata, 'IOS', $appuserid->push_token, $appuserid->device_type);
//                                    }
//                                } catch (\Exception $exc) {
//                                    CustomDbModel::CreateNotifiactionLog(2, $exc, $userdata);
//                                }
//                            } else {
//                                try {
//                                    $pushstatus = PushNotification::app('AndroidUsers')
//                                            ->to($appuserid->push_token)
//                                            ->send(array('notification' => array('body' => $notificationdata['notification_message'], "title" => $notificationdata['notification_title'])), array('time_to_live' => '2419100'));
//                                    CustomDbModel::CreateNotifiactionLog(1, $pushstatus->adapter->getresponse()->getparsedResponses(), $userdata, 'Android', $appuserid->push_token, $appuserid->device_type);
//                                } catch (\Exception $exc) {
//                                    CustomDbModel::CreateNotifiactionLog(2, $exc, $userdata);
//                                }
//                            }
//                        }
//                    }
//
//                    CustomDbModel::CreateNotifiactionLog(1, $notificationdata, $userdata);
//                }
//            }
//            DB::commit();
//        } catch (\Exception $ex) {
//            DB::rollback();
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Line No: ' . $ex->getLine() . "\r\n" . ' Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/NotificatinTrigger-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function CreateNotifiactionLog($logtype, $message, $userdata, $device = '', $pushtoken = '', $deliverystatus = '') {
//        if (isset($userdata->emailid)) {
//            $emaild = $userdata->emailid;
//        } else {
//            $emaild = '';
//        }
//        if ($logtype == 1) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . json_encode($message) . "\r\n" . 'App User_Id: ' . $userdata->id . "\r\n" . 'User Email: ' . $emaild . "\r\n" . 'Device: ' . $device . "\r\n" . 'PushToken: ' . $pushtoken . "\r\n" . 'Device Name: ' . $deliverystatus . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/push_Notification_SuccessLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        } else {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $message . "\r\n" . 'App User_Id: ' . $userdata->id . "\r\n" . 'User Email: ' . $emaild . "\r\n" . 'Device: ' . $device . "\r\n" . 'PushToken: ' . $pushtoken . "\r\n" . 'Device Name: ' . $deliverystatus . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/push_Notification_ErrorLog-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }
//
//    public static function sendTicketNotification($ticketID, $action, $userdata, $engineerID = NULL, $spareRequestID = NULL, $dispatchedBy = NULL) {
//        try {
//            $userDetails = array();
//            $query = DB::table('ticket');
//            $query->where('ticket.fk_company_id', $userdata->usermaping->company)->where('ticket.id', $ticketID);
//            $query->leftJoin('users', 'users.id', '=', 'ticket.fk_users_id')
//                    ->leftJoin('company', 'company.id', '=', 'ticket.fk_company_id')
//                    ->leftJoin('customers', 'customers.id', '=', 'ticket.fk_customers_id')
//                    ->leftJoin('customers_locations', 'customers_locations.id', '=', 'ticket.fk_customers_locations_id')
//                    ->leftJoin('ticket_asset_location', 'ticket_asset_location.fk_ticket_id', '=', 'ticket.id')
//                    ->leftJoin('downtime', function($join) {
//                        $join->on("downtime.fk_parent_id", "=", "ticket.id")
//                        ->on('downtime.type', '=', DB::raw("'1'"));
//                    })->leftJoin('asset', function($join) {
//                $join->on("asset.traceability_id", "=", "ticket.fk_equipment_traceability_id")
//                        ->on('asset.fk_company_id', '=', "ticket.fk_company_id");
//            });
//
//            $selectValue = array('asset.manufacturer_name', 'asset.category_name', 'asset.sub_category', 'asset.equipments_name', 'asset.equipment_model_name', 'asset.equipment_model_image', 'asset.serialnumber', 'customers.id AS customer_id', 'customers.company_name AS customer_name', 'customers.workorder_required as preApproval', 'customers_locations.id AS location_id', 'customers_locations.location_name', 'ticket.id', 'ticket.ticket_overdue', 'ticket.ticket_id', 'ticket.ticket_type', 'ticket.priority', 'ticket.ticket_comments', 'ticket.ticket_status', DB::raw('to_char((raised_on AT TIME ZONE \'' . getCurrentTimeZone() . '\'), \'YYYY-MM-DD HH24:MI:SS\') as ticket_date'), DB::raw('to_char(("' . DB::getTablePrefix() . 'ticket"."updated_on" AT TIME ZONE \'' . getCurrentTimeZone() . '\'), \'YYYY-MM-DD HH24:MI:SS\') as ticket_updated_date'), 'ticket.raised_on', 'ticket.raised_on as created_date', 'ticket.updated_on as updated_date', 'ticket.status', 'users.user_firstname', 'users.user_lastname', DB::raw(' CONCAT("' . DB::getTablePrefix() . 'users"."user_firstname", \' \', "' . DB::getTablePrefix() . 'users"."user_lastname") AS raisedby'), 'ticket.overdue_message', 'ticket.message', 'ticket.fk_company_id', 'ticket.fk_customers_id', "ticket.fk_serviceagency_id", 'ticket.ticket_overdue', 'ticket.overdue_date', 'ticket.overdue_message', 'ticket.work_order_status', 'company.features_workorder', 'ticket.ticket_work_order_s3_path as wrkOrderFilePath', 'downtime.id as downtimeid', 'downtime.downtime_hours', 'downtime.downtime_minutes', 'customers.contact_number as customer_contact_number', 'customers.contact_email as customer_contact_email', 'company.currency_type', 'users.contact_no as user_contact', 'users.emailid as user_email', 'ticket.working_in_problem', 'company.push_notification_preference', 'company.email_preference', 'ticket.fk_customers_locations_id', 'ticket.fk_users_id');
//            $sel = array(
//                DB::raw('(CASE 
//                    WHEN "' . DB::getTablePrefix() . 'ticket"."ticket_type" = 1
//                    THEN (
//                        SELECT "' . DB::getTablePrefix() . 'defect_types"."defect_type" AS defect
//                        FROM "' . DB::getTablePrefix() . 'defect_ticket" 
//                        LEFT JOIN "' . DB::getTablePrefix() . 'defect_types" ON "' . DB::getTablePrefix() . 'defect_types"."id" = "' . DB::getTablePrefix() . 'defect_ticket"."fk_defect_types_id"
//                        WHERE "' . DB::getTablePrefix() . 'defect_ticket"."fk_ticket_id" = "' . DB::getTablePrefix() . 'ticket"."id"
//                        LIMIT 1
//                    )
//                    WHEN "' . DB::getTablePrefix() . 'ticket"."ticket_type" = 3
//                    THEN (
//                        SELECT "' . DB::getTablePrefix() . 'equipment_spareparts"."spareparts_name" AS defect
//                        FROM "' . DB::getTablePrefix() . 'spareparts_ticket" 
//                        LEFT JOIN "' . DB::getTablePrefix() . 'spareparts_schedule" ON "' . DB::getTablePrefix() . 'spareparts_schedule"."id" = "' . DB::getTablePrefix() . 'spareparts_ticket"."fk_spareparts_schedule_id"
//                        LEFT JOIN "' . DB::getTablePrefix() . 'ticket_replacement" ON "' . DB::getTablePrefix() . 'ticket_replacement"."fk_spareparts_ticket_id" = "' . DB::getTablePrefix() . 'spareparts_ticket"."id"
//                        LEFT JOIN "' . DB::getTablePrefix() . 'schedule_replacement" ON "' . DB::getTablePrefix() . 'schedule_replacement"."id" = "' . DB::getTablePrefix() . 'ticket_replacement"."fk_schedule_replacement_id"
//                        LEFT JOIN "' . DB::getTablePrefix() . 'equipment_spareparts" ON "' . DB::getTablePrefix() . 'equipment_spareparts"."id" = "' . DB::getTablePrefix() . 'schedule_replacement"."fk_equipment_spareparts_id"
//                        WHERE "' . DB::getTablePrefix() . 'spareparts_ticket"."fk_ticket_id" = "' . DB::getTablePrefix() . 'ticket"."id"
//                        LIMIT 1
//                    )
//                    WHEN "' . DB::getTablePrefix() . 'ticket"."ticket_type" = 4
//                    THEN (
//                        SELECT "' . DB::getTablePrefix() . 'ticket"."task_title" AS defect FROM "' . DB::getTablePrefix() . 'ticket" tkt
//                         WHERE "tkt"."id" = "' . DB::getTablePrefix() . 'ticket"."id"
//                    )
//                    ELSE NULL END) as defect'));
//
//            if ($userdata->business_type == config('2iequipstatus.businesstype.equipment_user')) {
//                $latlongSelect = array(
//                    DB::raw("(CASE "
//                            . "WHEN " . DB::getTablePrefix() . "ticket_asset_location.current_longitude != '' AND " . DB::getTablePrefix() . "ticket_asset_location.current_lattitude != '' "
//                            . "THEN " . DB::getTablePrefix() . "ticket_asset_location.current_location "
//                            . "ELSE " . DB::getTablePrefix() . "customers_locations.geo_location END) as current_location")
//                );
//            } elseif ($userdata->business_type == config('2iequipstatus.businesstype.service_agency')) {
//                $latlongSelect = array(
//                    DB::raw("(CASE "
//                            . "WHEN " . DB::getTablePrefix() . "ticket_asset_location.current_longitude != '' AND " . DB::getTablePrefix() . "ticket_asset_location.current_lattitude != '' "
//                            . "THEN " . DB::getTablePrefix() . "ticket_asset_location.current_location "
//                            . "WHEN " . DB::getTablePrefix() . "customers_equipment_mapping.current_equipment_location_longitude <> '' "
//                            . "THEN " . DB::getTablePrefix() . "customers_equipment_mapping.current_equipment_address "
//                            . "ELSE (SELECT " . DB::getTablePrefix() . "customers.geo_location FROM " . DB::getTablePrefix() . "customers "
//                            . "WHERE " . DB::getTablePrefix() . "customers.id = " . DB::getTablePrefix() . "ticket.fk_customers_id ) END) as current_location")
//                );
//            } else {
//                $latlongSelect = array();
//            }
//            $selectedValue = array_merge($selectValue, $sel, $latlongSelect);
//
//            $query->select($selectedValue);
//            $ticketDetails = $query->first();
//            if (isset($ticketDetails) && !empty($ticketDetails)) {
//                $companyID = $ticketDetails->fk_company_id;
//                $customerID = $ticketDetails->fk_customers_id;
//                $locationID = $ticketDetails->fk_customers_locations_id;
//
//                if (isset($action) && !empty($action)) {// Actions
//                    if ($userdata->business_type == config('2iequipstatus.businesstype.service_agency')) {
//                        $getusersDetails = array(
//                            "selectcol" =>
//                            array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                            "leftjoin" => array(
//                                array('users', 'users.id', '=', 'customers_users_mapping.fk_users_id')
//                            ),
//                            "condition" => array('customers_users_mapping.fk_customers_id' => $customerID, 'customers_users_mapping.dels' => 0, 'users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'customers_users_mapping.fk_company_id' => $userdata->usermaping->company),
//                            "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.customer'))),
//                            "singleslect" => false
//                        );
//                        $userDetails = CustomDbModel::selectquerywithcustom('customers_users_mapping', $getusersDetails, false);
//                    } elseif ($userdata->business_type == config('2iequipstatus.businesstype.equipment_user')) {
//                        $getusersDetails = array(
//                            "selectcol" => array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                            "leftjoin" => array(
//                                array('users', 'users.id', '=', 'customers_locations_users_mapping.fk_users_id'),
//                            ),
//                            "condition" => array('customers_locations_users_mapping.fk_customers_locations_id' => $locationID, 'customers_locations_users_mapping.dels' => 0, 'users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'customers_locations_users_mapping.fk_company_id' => $userdata->usermaping->company),
//                            "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.loactionhead'), config('2iequipstatus.usertype.machineowner'))),
//                            "singleslect" => false
//                        );
//                        $userDetails = CustomDbModel::selectquerywithcustom('customers_locations_users_mapping', $getusersDetails, false);
//                    }
//
//                    $getclientUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.status' => 1, 'users.forgot_password' => '', 'fk_company_id' => $companyID, 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array("users.user_type" => array(config('2iequipstatus.usertype.standaloneserviceagency'))),
//                        "singleslect" => false
//                    );
//                    $clientUserDetails = CustomDbModel::selectquerywithcustom('users', $getclientUsersDetails, false);
//                    if (isset($clientUserDetails) && !empty($clientUserDetails)) {
//                        $userDetails = array_merge($userDetails, $clientUserDetails);
//                    }
//
//                    if (isset($engineerID) && !empty($engineerID) && $engineerID != NULL) {
//                        $getEngineerUsersDetails = array(
//                            "selectcol" =>
//                            array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id', 'ticket_engineer.tentative_date_time as tentative_date'),
//                            "leftjoin" => array(array('users', 'users.id', '=', 'service_engineer.fk_users_id'), array('ticket_engineer', 'ticket_engineer.fk_service_engineer_id', '=', 'service_engineer.id')),
//                            "condition" => array('users.dels' => 0, 'service_engineer.id' => $engineerID, 'ticket_engineer.fk_ticket_id' => $ticketID, 'users.status' => 1, 'users.forgot_password' => '', 'service_engineer.fk_company_id' => $userdata->usermaping->company),
//                            "singleslect" => true
//                        );
//                        $engineerDetails = CustomDbModel::selectquerywithcustom('service_engineer', $getEngineerUsersDetails, false);
//                        if (isset($engineerDetails) && !empty($engineerDetails)) {
//                            $engineerName = $engineerDetails->firstname . " " . $engineerDetails->lastname;
//                            $tentativeDate = $engineerDetails->tentative_date;
//                            $engDetails[] = $engineerDetails;
//                            $userDetails = array_merge($userDetails, $engDetails);
//                        }
//                    }
//                    $getRaisedUsersDetails = array(
//                        "selectcol" =>
//                        array('users.id as usrID', 'users.user_firstname as firstname', 'users.user_lastname as lastname', 'users.emailid as emailid', 'users.user_type', 'users.parent_id'),
//                        "leftjoin" => array(),
//                        "condition" => array('users.dels' => 0, 'users.id' => $ticketDetails->fk_users_id, 'users.status' => 1, 'users.forgot_password' => '', 'users.fk_company_id' => $userdata->usermaping->company),
//                        "where_in" => array(),
//                        "singleslect" => true
//                    );
//                    $raisedUserDetails = CustomDbModel::selectquerywithcustom('users', $getRaisedUsersDetails, false);
//                    if (isset($raisedUserDetails) && !empty($raisedUserDetails)) {
//                        $usrDetails[] = $raisedUserDetails;
//                        $userDetails = array_merge($userDetails, $usrDetails);
//                    }
//
//                    //pre($userDetails);
//                    if (isset($userDetails) && !empty($userDetails)) {
//                        $NotifyDetails = array();
//                        foreach ($userDetails as $key => $value) {
//                            $NotifyDetails[$value->usrID] = $value;
//                        }
//                        if (isset($NotifyDetails) && !empty($NotifyDetails)) {
//                            $replaceString = array("replaceTicketID" => isset($ticketDetails->ticket_id) ? $ticketDetails->ticket_id : "", "replaceDefect" => isset($ticketDetails->defect) ? $ticketDetails->defect : "", "replaceUserName" => isset($userdata->username) ? $userdata->username : "", "replaceCompanyName" => isset($userdata->settings->company_name) ? $userdata->settings->company_name : "", "replaceSpareRequestID" => (isset($spareRequestID) && $spareRequestID != NULL) ? $spareRequestID : "", "replaceDispatchedThrough" => (isset($dispatchedBy) && $dispatchedBy != NULL) ? $dispatchedBy : '', "replaceEngineerName" => (isset($engineerName) && !empty($engineerName)) ? $engineerName : "", "replaceLocationName" => isset($ticketDetails->current_location) ? $ticketDetails->current_location : "", "replaceTentativeReachTime" => (isset($tentativeDate) && !empty($tentativeDate)) ? $tentativeDate : "", "replaceTime" => createddatetimeonly(), "replaceAssetName" => isset($ticketDetails->equipments_name) ? $ticketDetails->equipments_name : "", "replaceModelName" => isset($ticketDetails->equipment_model_name) ? $ticketDetails->equipment_model_name : "", "replaceserialNumber" => isset($ticketDetails->serialnumber) ? $ticketDetails->serialnumber : "", "replaceCustomerName" => isset($ticketDetails->customer_name) ? $ticketDetails->customer_name : "");
//                            foreach ($NotifyDetails as $key => $value) {
//                                if ($value->usrID != $userdata->id) {
//                                    $ticketTitle = config('ticketnotifications.actions.' . $action . '.' . $value->user_type . '.title');
//                                    $ticketMessage = config('ticketnotifications.actions.' . $action . '.' . $value->user_type . '.message');
//                                    $ticketSubject = config('ticketnotifications.actions.' . $action . '.' . $value->user_type . '.subject');
//                                    if (isset($ticketTitle) && isset($ticketMessage) && !empty($ticketTitle) && !empty($ticketMessage)) {
//                                        $notifyTitle = notificationStringReplacement($ticketTitle, $replaceString);
//                                        $notifyMessage = notificationStringReplacement($ticketMessage, $replaceString);
//                                        $notificaion = array("fk_company_id" => $companyID, "notification_id" => $ticketDetails->ticket_id, "notification_title" => trim($notifyTitle), "notification_message" => trim($notifyMessage), "notification_reference_id" => $ticketID, "notification_type" => config('2iequipstatus.notificationtype.ticket'), "parent_id" => $value->parent_id, "user_type" => $value->user_type, 'foruser' => $value->usrID, "fk_users_id" => $value->usrID, "created_by" => $value->usrID, "updated_by" => $value->usrID, "created_on" => createddatetime(), "updated_on" => createddatetime(), "status" => 1, "dels" => 0, 'userdata' => $userdata);
//                                        //CustomDbModel::NotifictionAndPush($notificaion);
//                                        if (isset($value->emailid)) {
//                                            if ($ticketDetails->email_preference == 1) {
//                                                $data = array(
//                                                    "url" => str_replace('api/public', '', url('/')) . '/',
//                                                    "business_type" => $userdata->business_type,
//                                                    "ticket_id" => $ticketDetails->ticket_id,
//                                                    "defect" => $ticketDetails->defect,
//                                                    "working_in_problem" => config('2iequipstatus.workingConditionLabel.' . $ticketDetails->working_in_problem),
//                                                    "priority" => config('2iequipstatus.ticketPriorityLabel.' . $ticketDetails->priority),
//                                                    "raised_by" => $ticketDetails->raisedby,
//                                                    "raised_on" => $ticketDetails->ticket_date,
//                                                    "asset_name" => $ticketDetails->equipments_name,
//                                                    "equipment_model_name" => $ticketDetails->equipment_model_name,
//                                                    "serialnumber" => $ticketDetails->serialnumber,
//                                                    "customer_name" => $ticketDetails->customer_name,
//                                                    "location_name" => $ticketDetails->location_name,
//                                                    "assinged_by" => $userdata->username,
//                                                    "assinged_on" => createddatetime(),
//                                                    "engineerName" => (isset($engineerName) && !empty($engineerName)) ? $engineerName : ""
//                                                );
//                                                if (isset($engineerDetails) && !empty($engineerDetails)) {
//                                                    $data['engineerDetails'] = true;
//                                                } else {
//                                                    $data['engineerDetails'] = false;
//                                                }
//                                               
//                                                //$email = $value->emailid;
//                                                $email = 'thirupathi@vijayglobal.com';
//                                                $subject = notificationStringReplacement($ticketSubject, $replaceString);
//                                                try {
//                                                    Mail::queue('emails.emailCommonTemplate', $data, function ($message) use ($email, $subject) {
//                                                        $message->from('noreply@innothinkglobal.com', 'InnoAssist');
//                                                        $message->to($email)->subject($subject);
//                                                    });
//                                                } catch (\Exception $ex) {
//                                                    $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//                                                    file_put_contents(env("APP_LOG_PATH") . "/TicketNotification-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//                                                }
//                                            }
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        } catch (\Exception $ex) {
//            $error_log_message = PHP_EOL . '------------' . "\r\n" . 'Created At: ' . createddatetime() . "\r\n" . ' Notification Message: ' . (string) $ex . "\r\n" . '------------' . PHP_EOL;
//            file_put_contents(env("APP_LOG_PATH") . "/TicketNotification-DB-" . createddate() . ".log", $error_log_message, FILE_APPEND);
//        }
//    }

}
