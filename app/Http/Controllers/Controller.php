<?php

namespace App\Http\Controllers;
use DB;
use Auth;
use App\Writer;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController

{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $childAcounts = array();

    public function getNextValue(array $values, $lastValue)
    {
        if ($lastValue == end($values)) {
            return '';
        } else {
            $lastIndex = array_search($lastValue, $values);
            $nextIndex = ($lastIndex + 1) % count($values);
            return $values[$nextIndex];
        }
    }

    public function getURLType()
    {
        $url_type = 'user';

        if (Auth::user()->user_type == 'Admin') {
            $url_type = 'admin';
        } else if (Auth::user()->user_type == 'Reseller') {
            $url_type = 'reseller';
        } else if(Auth::user()->user_type == 'Support') {
            $url_type = 'support';
        }

        return $url_type;
    }

    public function getAssignedUserIdForDevice($device_id)
    {
        $master_id = Auth::user()->id;
        $device_details = DB::table('devices')->where('id', $device_id)->first();
        $assign_to_ids = $device_details->assign_to_ids;
        $aids = explode(',', $assign_to_ids);
        $uid = $device_details->user_id;

        if (count($aids) > 1) {
            $next_id = self::getNextValue($aids, $master_id);
            if ($next_id) {
                $uid = $next_id;
            }
        }

        return $uid;
    }

    public function getAssignsIdsForChangeDeviceUser($uid, $assign_to_ids, $rem_self)
    {
        $child_Accs = self::getAllChildAccounts($uid);
        $acc_ids = array();
        if ($rem_self == 'yes') {
            $acc_ids[] = $uid;
        }

        if (count($child_Accs) > 0) {
            foreach ($child_Accs as $child_Acc) {
                $acc_ids[] = $child_Acc['uid'];
            }
        }

        $assign_to_ids = explode(',', $assign_to_ids);

        $resultArray = array_diff($assign_to_ids, $acc_ids);
        return implode(',', $resultArray);
    }

    public function getDeviceAssignToList($device_id)
    {
        $cu_id = Auth::user()->id;

        $device_details = DB::table('devices')->where('id', $device_id)->first();
        $assign_to_ids = $device_details->assign_to_ids;

        $aids = explode(',', $assign_to_ids);

        if (Auth::user()->user_type == 'Admin') {
            $aids = array($cu_id);
        } else {
            if (!in_array($cu_id, $aids)) {
                $aids[] = $cu_id;
            }

            $child_Accs = self::getAllChildAccounts($cu_id);
            if (Count($child_Accs) > 0) {
                foreach ($child_Accs as $child_Acc) {
                    if (($key = array_search($child_Acc['uid'], $aids)) !== false) {
                        unset($aids[$key]);
                    }
                }
            }
        }

        $aids = implode(',', $aids);
        return $aids;
    }

    public function getUserParent($uid)
    {
        $userinfo = Writer::select('*')->where([['id', '=', $uid]])->first();
        return $userinfo->created_by;
    }

    public function getAccountInfo($uid)
    {
        $userinfo = Writer::select('*')->where([['id', '=', $uid]])->first();
        return $userinfo;
    }

    public function getDirectChildAccounts($uid)
    {
        $users = Writer::select('*')->where([['created_by', '=', $uid], ['is_deleted', '=', 0]])->get();

        return $users;
    }

    public function getAllChildAccounts($uid)
    {
        $childAccs = self::getDirectChildAccounts($uid);
        self::recChildAccounts($childAccs, $uid, array());
        return $this->childAcounts;
    }

    public function recChildAccounts($accounts, $uid, $wholedata)
    {
        if (count($accounts) > 0) {
            foreach ($accounts as $account) {
                $this->childAcounts[] = array('uid' => $account->id, 'user_type' => $account->user_type, 'created_by' => $account->created_by);
                if ($account->user_type == 'Reseller') {
                    $childAccs = self::getDirectChildAccounts($account->id);
                    self::recChildAccounts($childAccs, $account->id, $wholedata);
                }
            }
        }
    }

    public function shiftChildDevicesAsUnassignToParent($child_Accs, $uid)
    {
        $parent_acc = self::getUserParent($uid);
        if ($parent_acc == 1) /// PARENT IS ADMIN
        {
            $mid = $parent_acc;
            $uid = NULL;
        } else {
            $mid = self::getUserParent($parent_acc); /// GET PARENT OF PARENT ACCOUNT
            $uid = $parent_acc;
        }

        if (count($child_Accs) > 0) {
            foreach ($child_Accs as $child_Acc) {
                $devices = DB::table('devices')->where('devices.user_id', $child_Acc['uid'])->get();

                foreach ($devices as $dkey => $device) {
                    $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($mid, $device->assign_to_ids, 'no');
                    DB::table('devices')->where('id', $device->id)->update(['master_id' => $mid, 'user_id' => $uid, 'assign_to_ids' => $new_assing_ids]);
                }
            }
        }
    }

    public function shiftOwnDevicesAsUnassignToParent($user_id)
    {
        $parent_acc = self::getUserParent($user_id);
        if ($parent_acc == 1) /// PARENT IS ADMIN
        {
            $mid = $parent_acc;
            $uid = NULL;
        } else {
            $mid = self::getUserParent($parent_acc); /// GET PARENT OF PARENT ACCOUNT
            $uid = $parent_acc;
        }

        $devices = DB::table('devices')->where('devices.user_id', $user_id)->get();

        foreach ($devices as $dkey => $device) {
            $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($mid, $device->assign_to_ids, 'no');
            DB::table('devices')->where('id', $device->id)->update(['master_id' => $mid, 'user_id' => $uid, 'assign_to_ids' => $new_assing_ids]);
        }
    }

    public function shiftChildDevicesAsAssignToSame($child_Accs, $uid)
    {
        $parent_acc = self::getUserParent($uid);
        if ($parent_acc == 1) /// PARENT IS ADMIN
        {
            $mid = $parent_acc;
        } else {
            $mid = self::getUserParent($parent_acc); /// GET PARENT OF PARENT ACCOUNT
        }

        if (count($child_Accs) > 0) {
            foreach ($child_Accs as $child_Acc) {
                $devices = DB::table('devices')->where('devices.user_id', $child_Acc['uid'])->get();

                foreach ($devices as $dkey => $device) {
                    $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($mid, $device->assign_to_ids, 'no');
                    DB::table('devices')->where('id', $device->id)->update(['master_id' => $mid, 'user_id' => $uid, 'assign_to_ids' => $new_assing_ids]);
                }
            }
        }
    }


    public function shiftDirectChildAccToParent($directChildAccs, $uid)
    {
        $parent_acc = self::getUserParent($uid);



        if (count($directChildAccs) > 0) {
            foreach ($directChildAccs as $directChildAcc) {
                $userinfo = self::getAccountInfo($directChildAcc->id);

                DB::table('writers')->where('id', $directChildAcc->id)->update(['created_by' => $parent_acc]);

                if ($userinfo->user_type == 'Reseller') {
                    $devices = DB::table('devices')->where('devices.user_id', $directChildAcc->id)->get();

                    foreach ($devices as $dkey => $device) {
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser($parent_acc, $device->assign_to_ids, 'no');

                        DB::table('devices')->where('id', $device->id)->update(['master_id' => $parent_acc, 'assign_to_ids' => $new_assing_ids]);
                    }
                }


                if ($userinfo->user_type == 'Reseller') {
                    $child_Accs = self::getAllChildAccounts($directChildAcc->id);

                    if (count($child_Accs) > 0) {
                        foreach ($child_Accs as $child_Acc) {
                            $devices = DB::table('devices')->where('devices.user_id', $child_Acc['uid'])->get();

                            foreach ($devices as $dkey => $device) {
                                $n_assing_ids = $device->assign_to_ids;
                                $n_assing_ids = explode(',', $n_assing_ids);
                                $n_assing_ids = array_diff($n_assing_ids, array($uid));
                                $n_assing_ids = array_values($n_assing_ids);
                                $n_assing_ids = implode(',', $n_assing_ids);

                                DB::table('devices')->where('id', $device->id)->update(['assign_to_ids' => $n_assing_ids]);
                            }
                        }
                    }
                } else if ($userinfo->user_type == 'User') {
                    $devices = DB::table('devices')->where('devices.user_id', $directChildAcc->id)->get();
                    foreach ($devices as $dkey => $device) {
                        $n_assing_ids = $device->assign_to_ids;
                        $n_assing_ids = explode(',', $n_assing_ids);
                        $n_assing_ids = array_diff($n_assing_ids, array($uid));
                        $n_assing_ids = array_values($n_assing_ids);
                        $n_assing_ids = implode(',', $n_assing_ids);

                        DB::table('devices')->where('id', $device->id)->update(['assign_to_ids' => $n_assing_ids]);
                    }
                }
            }
        }
    }

    public function delAllChildAccounts($child_Accs)
    {
        foreach ($child_Accs as $child_Acc) {
            DB::table('writers')->where('id', $child_Acc['uid'])->update(['is_deleted' => 1]);
        }
    }


    public function manageEditDelAccs($uid, $rdata, $action_type)
    {
     
        if ($action_type == 'edit') {
            $acc_type_changed = $rdata['acc_type_changed'];

            if ($acc_type_changed == 'r_t_u') /// Reseller to User
            {
                $child_Accs = self::getAllChildAccounts($uid);

                if ($rdata['del_type'] == 'del_all') /// DELETE ALL CHILD ACCOUNTS
                {

                    if ($rdata['shift_type'] == 'parent_account') {
                        self::shiftChildDevicesAsUnassignToParent($child_Accs, $uid);
                    } else if ($rdata['shift_type'] == 'own_parent_account') {
                        self::shiftOwnDevicesAsUnassignToParent($uid);
                        self::shiftChildDevicesAsUnassignToParent($child_Accs, $uid);
                    } else if ($rdata['shift_type'] == 'same_acc') {
                        self::shiftChildDevicesAsAssignToSame($child_Accs, $uid);
                    }

                    self::delAllChildAccounts($child_Accs);
                } else if ($rdata['del_type'] == 'shift_all') /// SHIFT ALL DIRECT CHILDS TO PARENT
                {
                  
                    $directChildAccs = self::getDirectChildAccounts($uid);
                    self::shiftDirectChildAccToParent($directChildAccs, $uid);
                }
            }
        } else if ($action_type == 'delete') {
            $user_type = $rdata['user_type'];

            if ($user_type == 'Reseller') /// DELETE RESELLER
            {
                $del_type = $rdata['del_type'];

                if ($del_type == 'del_all') /// DELETE ALL CHILD ACCOUNTS
                {
                    $child_Accs = self::getAllChildAccounts($uid);
                    self::shiftOwnDevicesAsUnassignToParent($uid);
                    self::shiftChildDevicesAsUnassignToParent($child_Accs, $uid);
                    self::delAllChildAccounts($child_Accs);
                } else if ($del_type == 'shift_all') /// SHIFT ALL DIRECT CHILDS TO PARENT
                {
                    self::shiftOwnDevicesAsUnassignToParent($uid);
                    $directChildAccs = self::getDirectChildAccounts($uid);
                    self::shiftDirectChildAccToParent($directChildAccs, $uid);
                }
            } else if ($user_type == 'User') /// DELETE USER
            {
                self::shiftOwnDevicesAsUnassignToParent($uid);
            }
        }
    }

    public function linkResellerAccount($uid, $reseller_id)
    {

        $userinfo = self::getAccountInfo($reseller_id);

        DB::table('writers')->where('id', $reseller_id)->update(['created_by' => $uid]);

        $child_Accs = array();
        if ($userinfo->user_type == 'Reseller') {
            $res_user = array(array('main' => 'yes', 'uid' => $reseller_id, 'user_type' => 'Reseller', 'created_by' => $uid));
            $child_Accs = self::getAllChildAccounts($reseller_id);
            $child_Accs = array_merge($res_user, $child_Accs);

            if (count($child_Accs) > 0) {
                foreach ($child_Accs as $child_Acc) {
                    $devices = DB::table('devices')->where('devices.user_id', $child_Acc['uid'])->get();

                    foreach ($devices as $dkey => $device) {
                        if (isset($child_Acc['main']) && $child_Acc['main'] == 'yes') {
                            $new_assing_ids = $device->assign_to_ids . ',' . $uid;
                            DB::table('devices')->where('id', $device->id)->update(['master_id' => $uid, 'assign_to_ids' => $new_assing_ids]);
                        } else {
                            $old_assign_ids = explode(',', $device->assign_to_ids);
                            $dindex = array_search($reseller_id, $old_assign_ids);
                            if ($dindex !== false) {
                                array_splice($old_assign_ids, $dindex, 0, $uid);
                            }

                            $new_assing_ids = array_values($old_assign_ids);
                            $new_assing_ids = implode(',', $new_assing_ids);
                            DB::table('devices')->where('id', $device->id)->update(['assign_to_ids' => $new_assing_ids]);
                        }
                    }
                }
            }
        } else if ($userinfo->user_type == 'User') {
            $devices = DB::table('devices')->where('devices.user_id', $reseller_id)->get();
            foreach ($devices as $dkey => $device) {
                $new_assing_ids = $device->assign_to_ids . ',' . $uid;
                DB::table('devices')->where('id', $device->id)->update(['master_id' => $uid, 'assign_to_ids' => $new_assing_ids]);
            }
        }
    }
}
