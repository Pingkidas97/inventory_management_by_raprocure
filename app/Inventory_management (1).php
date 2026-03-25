<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Inventory_management extends MY_Controller {
    public $Head_Search_Div;
    function __construct(){
        parent::__construct();
        $this->isUserLoggedin();
        $this->load->model('inventory_management_model');
        $this->load->model('search_model');
        //$this->access = getBuyerPermissionSession();
        $users          =   $this->session->userdata('auth_user');
        $child_branchs   =   array();
        if($users['parent_id'] != '') {
            $user_id        =   $users['parent_id'];
            $child_branchs   =   getBuyerUserBranchIdOnly();
        } else {
            $user_id   =  $users['users_id'];
        }
        $branch_data        =   $this->inventory_management_model->get_branch_data($user_id);
        if(isset($branch_data) && !empty($branch_data)){
            if(isset($child_branchs) && !empty($child_branchs)){
                $new_branch_data = $branch_data;
                foreach($new_branch_data as $nbrn_key => $nbrn_row){
                    if(!in_array($nbrn_row->id,$child_branchs)){
                        unset($new_branch_data[$nbrn_key]);
                    }
                }
                if(isset($new_branch_data) && !empty($new_branch_data)){
                }
                else{
                    $this->session->set_flashdata('invalid_url', "Branch Not Found");
                    redirect(base_url('user/myaccount'));
                }
            }
        }
        else{
            redirect(base_url('user/myaccount'));
        }
        if(isset($users['is_inventory_enable']) && $users['is_inventory_enable']==1){

        }
        else{
            $this->session->set_flashdata('invalid_url', "Branch Not Found");
            redirect(base_url('inventory_api'));
        }
        checkBuyerProfileVerified();
        $this->Head_Search_Div = true;
    }
    public function index(){
        $users              =   $this->session->userdata('auth_user');
        $is_parents_login   =   false;
        $iseditindent       =   false;
        if($users['parent_id'] != '') {
            $user_id                =   $users['parent_id'];
        } else {
            $user_id                =   $users['users_id'];
            $is_parents_login       =   true;
            $iseditindent           =   true;
        }
        $access = getBuyerPermissionSession();
        if($access['INDENT_APPROVE']['INDENT_APPROVE_MANAGEMENT']['create']=='yes'){
            $iseditindent           =   true;
        }
        $data['page_title']         =   "Inventory Management";
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['locations']          =   $this->inventory_management_model->get_locations($user_id);
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        // if(isset($buyer_currency) && $buyer_currency!=''){
        //     $currency_qry = $this->db->select('id,currency_name,currency_symbol')->get_where('tbl_currency',array('id' => $buyer_currency));
        //     if($currency_qry->num_rows()){
        //         $data['currency_list']     =   $currency_qry->row()->;
        //     }
        // }
        $data['currency_list']          =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']         =   $buyer_currency;
        $data['uom_list']               =   getUOMList();
        $data['inventory_type']         =   GetInventoryType();
        $data['issued_types']           =   GetIssuedTypes();
        $data['is_parents_login']       =   $is_parents_login;
        $data['iseditindent']           =   $iseditindent;
        $data['current_login_usrname']  =   $users['first_name'];
        $this->load->view('inventory_management/list',$data);
    }
    public function get_categorys_list($categorys=NULL){
        $cat_id =   array();
        if($categorys){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $categorys, 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        return $cat_id;
    }
    public function get_inventory_data()
    {
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs            =   array();
        $nonactiveindents   =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            //===Total Indent Qty ===//
            $get_indent_respose =   $this->get_indent_qty_data($invarrs);
            $totindqty          =   $get_indent_respose['totindqty'];
            $nonactiveindents   =   $get_indent_respose['nonactiveindents'];
            //===Total Indent Qty ===//
        }

        $data1      =   [];
        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $already_fetch_rfq              =   array();

            $response_get_rfq_datas         =   $this->get_rfq_datas($invarrs);
            $already_fetch_rfq              =   $response_get_rfq_datas['already_fetch_rfq'];
            $close_rfq_id_arr               =   $response_get_rfq_datas['close_rfq_id_arr'];
            $rfq_ids_against_inventory_id   =   $response_get_rfq_datas['rfq_ids_against_inventory_id'];
            $rfq_qty                        =   $response_get_rfq_datas['rfq_qty'];
            if(isset($already_fetch_rfq) && !empty($already_fetch_rfq)){
                $response_close_rfq_id_rfq_ids_against_inventory_id =   $this->get_close_rfq_id_rfq_ids_against_inventory_id($invarrs,$already_fetch_rfq);
                $close_rfq_id_arr               =   $response_close_rfq_id_rfq_ids_against_inventory_id['close_rfq_id_arr'];
                $rfq_ids_against_inventory_id   =   $response_close_rfq_id_rfq_ids_against_inventory_id['rfq_ids_against_inventory_id'];

            }
            //pr($close_rfq_id_arr); die;
            //===For order RFQ===//
            $response_rfq_tot_price_id_rfq_tot_price_inv_id         =   $this->get_rfq_tot_price_id_rfq_tot_price_inv_id($invarrs);
            $rfq_tot_price_id                                       =   $response_rfq_tot_price_id_rfq_tot_price_inv_id['rfq_tot_price_id'];
            $rfq_tot_price_inv_id                                   =   $response_rfq_tot_price_id_rfq_tot_price_inv_id['rfq_tot_price_inv_id'];

            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            //pr($close_rfq_id_arr); die;
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $response_close_price_ids_get_inv_ids_price         =   $this->get_close_price_ids_get_inv_ids_price($close_rfq_id_arr,$rfq_ids_against_inventory_id);
                $close_price_ids                                    =   $response_close_price_ids_get_inv_ids_price['close_price_ids'];
                $get_inv_ids_price                                  =   $response_close_price_ids_get_inv_ids_price['get_inv_ids_price'];

            }
            //pr($close_price_ids); die;
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $response_closed_order_final_close_order        =   $this->get_closed_order_final_close_order($close_price_ids,$get_inv_ids_price);
                $closed_order                                   =    $response_closed_order_final_close_order['closed_order'];
                $final_close_order                              =    $response_closed_order_final_close_order['final_close_order'];

            }
            //pr($final_close_order); die;
            //===Closed RFQ Qty=====//
            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $response_order_price_ids_place_order_inv_ids_price         =   $this->get_order_price_ids_place_order_inv_ids_price($rfq_tot_price_id,$rfq_tot_price_inv_id);
                $order_price_ids                                            =   $response_order_price_ids_place_order_inv_ids_price['order_price_ids'];
                $place_order_inv_ids_price                                  =   $response_order_price_ids_place_order_inv_ids_price['place_order_inv_ids_price'];

            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $response_place_order_final_place_order         =   $this->get_place_order_final_place_order($order_price_ids,$place_order_inv_ids_price);
                $place_order                                    =   $response_place_order_final_place_order['place_order'];
                $final_place_order                              =   $response_place_order_final_place_order['final_place_order'];

            }
            //pr($final_place_order); die;
            //===Place Order====//
            //===GRN====//
            $new_grn_wpo_arr    =   array();
            $grn_manual_po_arr  =   array();

            $response_new_grn_wpo_arr           =   $this->get_new_grn_wpo_arr($invarrs);
            $new_grn_wpo_arr                    =   $response_new_grn_wpo_arr;

            //===GRN MANUAL PO====//
            //===GRN WPO====//

            $grn_wpo_arr    =   array();
            $grn_mpo_arr    =   array();
            $grn_wopo_arr   =   array();
            $grn_stock_arr  =   array();

            $response_grn_wpo_grn_mpo_grn_wopo_grn_stock    =   $this->get_grn_wpo_grn_mpo_grn_wopo_grn_stock($invarrs);
            $grn_wpo_arr    =   $response_grn_wpo_grn_mpo_grn_wopo_grn_stock['grn_wpo'];
            $grn_mpo_arr    =   $response_grn_wpo_grn_mpo_grn_wopo_grn_stock['grn_mpo'];
            $grn_wopo_arr   =   $response_grn_wpo_grn_mpo_grn_wopo_grn_stock['grn_wopo'];
            $grn_stock_arr  =   $response_grn_wpo_grn_mpo_grn_wopo_grn_stock['grn_stock'];
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $response_issued_arr    =   $this->get_issued_arr($invarrs);
            $issued_arr             =   $response_issued_arr;

            //===Issued===//

            //====Issued Return===//
            $issued_return_arr = array();
            $response_issued_return_arr     =   $this->get_issued_return_arr($invarrs);
            $issued_return_arr              =   $response_issued_return_arr;
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $response_stock_return_arr      =   $this->get_stock_return_arr($invarrs);
            $stock_return_arr               =   $response_stock_return_arr;
            //===Stock Return=====//
            //===get manual po details==//
            $mpo_datas_arr = array();
            $response_mpo_datas_arr     =   $this->get_mpo_datas_arr($invarrs);
            $mpo_datas_arr              =   $response_mpo_datas_arr;
        }
        // pr($new_grn_wpo_arr);
        // pr($grn_qty);die;
        // pr($result);



       foreach ($result as $key => $val) {
            // Initializing values with default 0 using null coalescing operator (??)
            $total_quantity = $totindqty[$val->id] ?? 0;
            $total_RFQ = ($rfq_qty[$val->id] ?? 0) + ($final_close_order[$val->id] ?? 0);
            $totl_order = $final_place_order[$val->id] ?? 0;

            // GRN quantities
            $new_grn_qty = $new_grn_wpo_arr[$val->id] ?? 0;
            $grn_qty = $grn_wpo_arr[$val->id] ?? 0;
            $grn_qty_mpo = $grn_mpo_arr[$val->id] ?? 0;
            $grn_qty_wop = $grn_wopo_arr[$val->id] ?? 0;
            $grn_qty_stok = $grn_stock_arr[$val->id] ?? 0;
            $grn_qty_manual_po = $grn_manual_po_arr[$val->id] ?? 0;

            // Issued quantities
            $issued_qty = $issued_arr[$val->id] ?? 0;
            $issued_return_qty = $issued_return_arr[$val->id] ?? 0;
            $stock_return_qty = $stock_return_arr[$val->id] ?? 0;

            // MPO Data
            $mpo_datas_qty = $mpo_datas_arr[$val->id] ?? 0;

            // Calculate stock
            $mystock = ($val->opening_stock + $grn_qty + $grn_qty_wop + $grn_qty_stok + $issued_return_qty + $grn_qty_mpo)
                        - ($issued_qty + $stock_return_qty);

            // Inventory deletion flag
            $is_del_invs = '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="1">';
            if ($val->opening_stock >= 1 || $val->is_indent == 1 || $grn_qty_manual_po >= 1 || $mpo_datas_qty >= 1) {
                //$is_del_invs = '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="0">';
            }

            // Checkbox handling
            $inventory_all_checkbox = isset($_COOKIE['inventory_all_checkbox']) ? explode(',', $_COOKIE['inventory_all_checkbox']) : [];
            $checked_invs = in_array($val->id, $inventory_all_checkbox) ? 'checked' : '';

            // Non-active indent class
            $nonactvcls = isset($nonactiveindents[$val->id]) ? 'smtnonactvcls' : '';

            // Table row generation
            $sub_array = [];
            $sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$val->id.'" class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="close_indent_tds('.$val->id.')"><i class="bi bi-dash-lg"></i></span>
                            <span data-toggle="collapse" style="cursor: pointer" id="plus_'.$val->id.'" class="pr-2 accordion_parent accordion_parent_'.$val->id.' '.$nonactvcls.'" tab-index="'.$val->id.'" onclick="open_indent_tds('.$val->id.')"><i class="bi bi-plus-lg"></i></span>
                            <input type="checkbox" class="inventory_chkd" name="inv_checkbox" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'" '.$checked_invs.'>';

            // Product name with minimum quantity badge
            $product_link = '<a href="'.base_url().'inventory_management/product_wise_stock_ledger/'.$val->id.'">'.$val->prod_name.'</a>';
            if (isset($val->indent_min_qty) && $val->indent_min_qty > 0 && $mystock <= $val->indent_min_qty) {
                $product_link .= '<button type="button" class="btn position-relative" style="color:white !important; background: #015294 !important; border-color:#015294 !important;">Min Qty
                                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background:red!important; color: white !important;">'.$val->indent_min_qty.'</span>
                                  </button>';
            }
            $sub_array[] = $product_link;

            // Category and other details
            $sub_array[] = $val->cat_name;
            $sub_array[] = (strlen($val->buyer_product_name) <= 20) ? $val->buyer_product_name : substr($val->buyer_product_name, 0, 20) . '<i class="bi bi-info-circle-fill" title="'.$val->buyer_product_name.'"></i>';
            $sub_array[] = (strlen($val->specification) <= 20) ? $val->specification : substr($val->specification, 0, 20) . '<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] = (strlen($val->size) <= 20) ? $val->size.$is_del_invs : substr($val->size, 0, 20) . '<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>'.$is_del_invs;
            $sub_array[] = (strlen($val->product_brand) <= 20) ? $val->product_brand : substr($val->product_brand, 0, 20) . '<i class="bi bi-info-circle-fill" title="'.$val->product_brand.'"></i>';
            $sub_array[] = (strlen($val->inventory_grouping) <= 20) ? $val->inventory_grouping : substr($val->inventory_grouping, 0, 20) . '<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';

            // Stock and UOM
            $sub_array[] = round($mystock, 2);
            $sub_array[] = $val->uom_name.'<input type="hidden" id="inventory_addedby_name_'.$val->id.'" value="'.$val->first_name.'">';

            // Total Indent Quantity
            $sub_array[] = '<span id="total_indent_qty_'.$val->id.'">'.round($total_quantity, 2).'</span>';

            // RFQ Quantity
            $sub_array[] = ($total_RFQ > 0) ? '<span class="active_rfq_quan" id="total_order_rfq_qty_'.$val->id.'" onclick="show_rfq_active('.$val->id.')">'.round($total_RFQ, 2).'</span>' : '0';

            // Placed Order Quantity
            $sub_array[] = ($totl_order > 0) ? '<span class="active_order_quan" id="total_inven_orders_quan'.$val->id.'" onclick="show_order_model('.$val->id.')">'.round($totl_order, 2).'</span>' : '0';

            // GRN Quantity
            $sub_array[] = '<span class="grn_qunty" id="inven_grn_quan_'.$val->id.'" onclick="show_grn_model('.$val->id.')">'.round($new_grn_qty, 2).'</span>
                            <span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';

            $data1[] = $sub_array;
        }
        // pr($data1); die;
        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  count($data1),
            "recordsFiltered"   =>  $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function get_indent_qty_data($invarrs=array()){
        $ind_qry = $this->db->select('inventory_id, indent_qty, is_active')
                    ->where_in('inventory_id', $invarrs)
                    ->where('indent_qty >=', 0)
                    ->where('inv_status', 1)
                    ->where('is_deleted !=', 1)
                    ->get('indent_mgt');
        $resposne           =   [];
        $totindqty          =   [];
        $nonactiveindents   =   [];

        foreach ($ind_qry->result_array() as $inds_resp) {
            if ($inds_resp['is_active'] == 1) {
                $totindqty[$inds_resp['inventory_id']] =
                    (isset($totindqty[$inds_resp['inventory_id']]) ? $totindqty[$inds_resp['inventory_id']] : 0)
                    + round($inds_resp['indent_qty'], 2);
            } else {
                $nonactiveindents[$inds_resp['inventory_id']] = $inds_resp['inventory_id'];
            }
        }
        $resposne['totindqty']          =   $totindqty;
        $resposne['nonactiveindents']   =   $nonactiveindents;
        return $resposne;
    }

    public function get_rfq_datas($invarrs) {
        $query = $this->db->select([
                'MAX(id) AS id',
                'MAX(rfq_id) AS rfq_id',
                'MAX(inventory_id) AS inventory_id',
                'MAX(quantity) AS quantity',
                'MAX(buyer_rfq_status) AS buyer_rfq_status'
            ])
            ->from('tbl_rfq')
            ->where([
                'record_type' => 2,
                'inv_status' => 1
            ])
            ->where_in('inventory_id', $invarrs)
            ->group_by('variant_grp_id')
            ->get();

        $result = [
            'already_fetch_rfq' => [],
            'close_rfq_id_arr' => [],
            'rfq_ids_against_inventory_id' => [],
            'rfq_qty' => []
        ];

        foreach ($query->result_array() as $rfq_row) {
            $id = $rfq_row['id'];
            $inventory_id = $rfq_row['inventory_id'];
            $quantity = $rfq_row['quantity'];
            $status = $rfq_row['buyer_rfq_status'];

            $result['already_fetch_rfq'][$id] = $id;

            if (in_array($status, [8, 10])) {
                $result['close_rfq_id_arr'][$id] = $id;
                $result['rfq_ids_against_inventory_id'][$id] = $inventory_id;
            } else {
                $result['rfq_qty'][$inventory_id] = ($result['rfq_qty'][$inventory_id] ?? 0) + $quantity;
            }
        }

        return $result;
    }

    public function get_close_rfq_id_rfq_ids_against_inventory_id($invarrs, $already_fetch_rfq) {
        $query = $this->db->select([
                'MAX(id) AS id',
                'MAX(rfq_id) AS rfq_id',
                'MAX(inventory_id) AS inventory_id',
                'MAX(quantity) AS quantity',
                'MAX(buyer_rfq_status) AS buyer_rfq_status'
            ])
            ->from('tbl_rfq')
            ->where([
                'record_type' => 2,
                'inv_status' => 1
            ])
            ->where_in('inventory_id', $invarrs)
            ->where_in('buyer_rfq_status', ['8', '10'])
            ->where_not_in('id', $already_fetch_rfq)
            ->group_by('variant_grp_id')
            ->get();

        $close_rfq_id_arr = [];
        $rfq_ids_against_inventory_id = [];

        foreach ($query->result() as $rfq_row) {
            $close_rfq_id_arr[$rfq_row->id] = $rfq_row->id;
            $rfq_ids_against_inventory_id[$rfq_row->id] = $rfq_row->inventory_id;
        }

        return [
            'close_rfq_id_arr' => $close_rfq_id_arr,
            'rfq_ids_against_inventory_id' => $rfq_ids_against_inventory_id
        ];
    }

    public function get_rfq_tot_price_id_rfq_tot_price_inv_id($invarrs) {
        $query = $this->db->select('id, rfq_id, inventory_id, quantity, buyer_rfq_status')
            ->from('tbl_rfq')
            ->where([
                'record_type' => 2,
                'inv_status' => 1
            ])
            ->where_in('inventory_id', $invarrs)
            ->get();

        $rfq_tot_price_id = [];
        $rfq_tot_price_inv_id = [];

        foreach ($query->result() as $rfq_row) {
            $rfq_tot_price_id[$rfq_row->id] = $rfq_row->id;
            $rfq_tot_price_inv_id[$rfq_row->id] = $rfq_row->inventory_id;
        }

        return [
            'rfq_tot_price_id' => $rfq_tot_price_id,
            'rfq_tot_price_inv_id' => $rfq_tot_price_inv_id
        ];
    }

    public function get_close_price_ids_get_inv_ids_price($close_rfq_id_arr, $rfq_ids_against_inventory_id) {
        $query = $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $close_rfq_id_arr)
            ->get();

        $close_price_ids = [];
        $get_inv_ids_price = [];

        foreach ($query->result() as $rfq_prc_row) {
            $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
            $get_inv_ids_price[$rfq_prc_row->id] = $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] ?? '';
        }

        return [
            'close_price_ids' => $close_price_ids,
            'get_inv_ids_price' => $get_inv_ids_price
        ];
    }

    public function get_closed_order_final_close_order($close_price_ids, $get_inv_ids_price) {
        $query = $this->db->select('price_id, order_quantity')
            ->from('tbl_rfq_order')
            ->where_in('price_id', $close_price_ids)
            ->get();

        $closed_order = [];
        $final_close_order = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $rfq_ord) {
                $closed_order[$rfq_ord->price_id] = ($closed_order[$rfq_ord->price_id] ?? 0) + $rfq_ord->order_quantity;
            }

            foreach ($closed_order as $price_id => $quantity) {
                if (isset($get_inv_ids_price[$price_id])) {
                    $final_close_order[$get_inv_ids_price[$price_id]] = $quantity;
                }
            }
        }

        return [
            'closed_order'      => $closed_order,
            'final_close_order' => $final_close_order
        ];
    }

    public function get_order_price_ids_place_order_inv_ids_price($rfq_tot_price_id, $rfq_tot_price_inv_id) {
        $query = $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $rfq_tot_price_id)
            ->get();

        $order_price_ids = [];
        $place_order_inv_ids_price = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $rfq_prc_row) {
                $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                $place_order_inv_ids_price[$rfq_prc_row->id] = $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] ?? '';
            }
        }

        return [
            'order_price_ids' => $order_price_ids,
            'place_order_inv_ids_price' => $place_order_inv_ids_price
        ];
    }

    public function get_place_order_final_place_order($order_price_ids, $place_order_inv_ids_price) {
        $query = $this->db->select('price_id, order_quantity')
            ->from('tbl_rfq_order')
            ->where('order_status', '1')
            ->where_in('price_id', $order_price_ids)
            ->get();

        $place_order = [];
        $final_place_order = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $rfq_ord) {
                $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id])
                    ? $place_order[$rfq_ord->price_id] + $rfq_ord->order_quantity
                    : $rfq_ord->order_quantity;
            }

            foreach ($place_order as $crows_key => $crow_val) {
                $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]])
                    ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val)
                    : $crow_val;
            }
        }

        return [
            'place_order' => $place_order,
            'final_place_order' => $final_place_order
        ];
    }

    public function get_new_grn_wpo_arr($invarrs) {
        $query = $this->db->select('grn_qty, inventory_id, grn_type')
            ->from('grn_mgt')
            ->where('inv_status', 1)
            ->where('is_deleted', '0')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->get();

        $new_grn_wpo_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $grn_wp_mpo_res) {
                if ($grn_wp_mpo_res->grn_type == 1) {
                    $new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id] = isset($new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id])
                        ? $new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id] + $grn_wp_mpo_res->grn_qty
                        : $grn_wp_mpo_res->grn_qty;
                }
            }
        }

        return $new_grn_wpo_arr;
    }
    public function get_grn_wpo_grn_mpo_grn_wopo_grn_stock($invarrs) {
        $query = $this->db->select('grn_qty, inventory_id, grn_type')
            ->from('grn_mgt')
            ->where('is_deleted', '0')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '2', '3', '4'])
            ->get();

        $grn_wpo_arr = [];
        $grn_mpo_arr = [];
        $grn_wopo_arr = [];
        $grn_stock_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $grn_wp_wop_res) {
                switch ($grn_wp_wop_res->grn_type) {
                    case '1':
                        $grn_wpo_arr[$grn_wp_wop_res->inventory_id] =
                            isset($grn_wpo_arr[$grn_wp_wop_res->inventory_id])
                            ? $grn_wpo_arr[$grn_wp_wop_res->inventory_id] + $grn_wp_wop_res->grn_qty
                            : $grn_wp_wop_res->grn_qty;
                        break;
                    case '4':
                        $grn_mpo_arr[$grn_wp_wop_res->inventory_id] =
                            isset($grn_mpo_arr[$grn_wp_wop_res->inventory_id])
                            ? $grn_mpo_arr[$grn_wp_wop_res->inventory_id] + $grn_wp_wop_res->grn_qty
                            : $grn_wp_wop_res->grn_qty;
                        break;
                    case '2':
                        $grn_wopo_arr[$grn_wp_wop_res->inventory_id] =
                            isset($grn_wopo_arr[$grn_wp_wop_res->inventory_id])
                            ? $grn_wopo_arr[$grn_wp_wop_res->inventory_id] + $grn_wp_wop_res->grn_qty
                            : $grn_wp_wop_res->grn_qty;
                        break;
                    case '3':
                        $grn_stock_arr[$grn_wp_wop_res->inventory_id] =
                            isset($grn_stock_arr[$grn_wp_wop_res->inventory_id])
                            ? $grn_stock_arr[$grn_wp_wop_res->inventory_id] + $grn_wp_wop_res->grn_qty
                            : $grn_wp_wop_res->grn_qty;
                        break;
                }
            }
        }

        return [
            'grn_wpo' => $grn_wpo_arr,
            'grn_mpo' => $grn_mpo_arr,
            'grn_wopo' => $grn_wopo_arr,
            'grn_stock' => $grn_stock_arr
        ];
    }

    public function get_issued_arr($invarrs) {
        $query = $this->db->select('qty, inventory_id')
            ->from('issued_mgt')
            ->where('is_deleted', '0')
            ->where_in('inventory_id', $invarrs)
            ->get();

        $issued_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $issue_res) {
                $issued_arr[$issue_res->inventory_id] =
                    isset($issued_arr[$issue_res->inventory_id])
                    ? $issued_arr[$issue_res->inventory_id] + $issue_res->qty
                    : $issue_res->qty;
            }
        }

        return $issued_arr;
    }

    public function get_issued_return_arr($invarrs) {
        $query = $this->db->select('qty, inventory_id')
            ->from('issued_return_mgt')
            ->where('is_deleted', '0')
            ->where_in('inventory_id', $invarrs)
            ->get();

        $issued_return_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $issue_ret_res) {
                $issued_return_arr[$issue_ret_res->inventory_id] =
                    isset($issued_return_arr[$issue_ret_res->inventory_id])
                    ? $issued_return_arr[$issue_ret_res->inventory_id] + $issue_ret_res->qty
                    : $issue_ret_res->qty;
            }
        }

        return $issued_return_arr;
    }

    public function get_stock_return_arr($invarrs) {
        $query = $this->db->select('qty, inventory_id')
            ->from('tbl_return_stock')
            ->where('is_deleted', '0')
            ->where_in('inventory_id', $invarrs)
            ->get();

        $stock_return_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $stock_ret_res) {
                $stock_return_arr[$stock_ret_res->inventory_id] =
                    isset($stock_return_arr[$stock_ret_res->inventory_id])
                    ? $stock_return_arr[$stock_ret_res->inventory_id] + $stock_ret_res->qty
                    : $stock_ret_res->qty;
            }
        }

        return $stock_return_arr;
    }

    public function get_mpo_datas_arr($invarrs) {
        $query = $this->db->select('inventory_id')
            ->from('tbl_manual_po_order')
            ->where('order_status', '1')
            ->where_in('inventory_id', $invarrs)
            ->get();

        $mpo_datas_arr = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $tmpo_vals) {
                $mpo_datas_arr[$tmpo_vals->inventory_id] = $tmpo_vals->inventory_id;
            }
        }

        return $mpo_datas_arr;
    }

    public function get_inventory_for_manual_po(){
        if($this->input->is_ajax_request()){
            $inventories  =   $this->input->post('inventories',true);
            if($inventories && is_array($inventories)){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where_in('inv.id',$inventories);
                $this->db->order_by('tp.prod_name','ASC');
                $query = $this->db->get();
                if($query->num_rows()){

                    $data = $query->result();
                    $resp    =   array();

                    foreach ($data as $row) {
                        $item = array();
                        $item['inv_id'] = $row->id;
                        $item['comp_br_sp_inv_id'] = $row->comp_br_sp_inv_id;
                        $item['product_id'] = $row->product_id;
                        $item['specification'] = $row->specification;
                        $item['size'] = $row->size;
                        $item['uom'] = $row->uom;
                        $item['product_name'] = $row->prod_name;
                        $item['product_id'] = $row->product_id;
                        $item['uom_name'] = $row->uom_name;

                        $resp[] = $item; // Add the item to the response array
                    }
                    $res['status'] = 1;
                    $res['message'] = 'Inventory found';
                    $res['data'] = $resp;
                    $res['gst'] = $this->db->select('*')->from('tbl_tax')->where('status','Active')->get()->result();

                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function fetch_vendor_name_for_manual_po() {
        if ($this->input->is_ajax_request()) {
            $search_vendor_name = $this->input->post('search_vendor_name', true);

            if ($search_vendor_name) {
                // $this->db->select("store_id,store_name,store_address", false);
                // $this->db->from("tbl_store");
                // $this->db->like('store_name', $search_vendor_name);
                // $query = $this->db->get();
                $this->db->select("store.store_id,store.store_name,store.store_address", false);
                $this->db->from("tbl_store as store");
                $this->db->like('store.store_name', $search_vendor_name);
                $this->db->join("tbl_users as tu",'tu.store_id=store.store_id', 'LEFT');
                $this->db->join("tbl_user_seller_details as tus",'tus.user_id=tu.id AND tus.store_id=store.store_id', 'LEFT');
                $this->db->where('tus.status', 1);
                $this->db->where('tu.status', 1);
                $query = $this->db->get();


                if ($query->num_rows() > 0) {
                    $data = $query->result();
                    $resp = array();

                    foreach ($data as $row) {
                        $item = array();
                        $item['id'] = $row->store_id;
                        $item['name'] = $row->store_name;
                        $item['address'] = $row->store_address;
                        // Add any additional fields you need

                        $resp[] = $item; // Add the item to the response array
                    }

                    $res['status'] = 1;
                    $res['message'] = 'Vendor found';
                    $res['data'] = $resp;
                } else {
                    $res['status'] = 2;
                    $res['message'] = 'Error, Vendor not found';
                }
            } else {
                $res['status'] = 2;
                $res['message'] = 'Error, Vendor name cannot be empty';
            }

            echo json_encode($res);
            die;
        }
    }
    public function fetch_vendor_details_for_manual_po() {
        if ($this->input->is_ajax_request()) {
            $id = $this->input->post('id', true);
            if ($id) {
                $this->db->select("store.store_id,store.store_name,store.store_address,store.store_location,store.store_pin_code,tu.email_address,tu.mobile,tu.country_code,tus.organization_gstin,tus.organization_pan_number,ts.name as state_name ,ts.state_code,tc.city_name", false);
                $this->db->from("tbl_store as store");
                $this->db->join("tbl_users as tu",'tu.store_id=store.store_id', 'LEFT');
                $this->db->join("tbl_states as ts",'ts.id=store.state_name', 'LEFT');
                $this->db->join("tbl_cities as tc",'tc.id=store.store_city', 'LEFT');
                $this->db->join("tbl_user_seller_details as tus",'tus.user_id=tu.id AND tus.store_id=store.store_id', 'LEFT');
                $this->db->where('store.store_id', $id);
                $this->db->where('tus.status', 1);

                $query = $this->db->get();

                if ($query->num_rows() > 0) {
                    $data = $query->result();
                    $resp = array();

                    foreach ($data as $row) {
                        $item = array();
                        $item['id'] = $row->store_id;
                        $item['name'] = $row->store_name;
                        $item['address'] = $row->store_address.','.$row->store_location;
                        $item['city'] = $row->city_name;
                        $item['pincode'] = $row->store_pin_code;
                        $item['state'] = $row->state_name;
                        $item['state_code'] = $row->state_code;
                        $item['gst'] = $row->organization_gstin;
                        $item['pan'] = $row->organization_pan_number;
                        $item['email'] = $row->email_address;
                        $item['ph'] = $row->mobile;
                        $item['country_code'] = $row->country_code;
                        $resp[] = $item;
                    }

                    $res['status'] = 1;
                    $res['message'] = 'Vendor found';
                    $res['data'] = $resp;
                } else {
                    $res['status'] = 2;
                    $res['message'] = 'Error, Vendor not found';
                }
            } else {
                $res['status'] = 2;
                $res['message'] = 'Error, Vendor name cannot be empty';
            }

            echo json_encode($res);
            die;
        }
    }
    public function generate_manual_po(){
        if ($this->input->is_ajax_request()) {
            // Get the posted data
            $data = $this->input->post();
            if (empty($data['vendor_id'])) {
                echo json_encode(['status' => '2', 'message' => 'Vendor ID cannot be empty.']);
                return;
            }
            if (empty($data['order_payment_term'])) {
                echo json_encode(['status' => '2', 'message' => 'Payment Term cannot be empty.']);
                return;
            }
            if (empty($data['order_price_basis'])) {
                echo json_encode(['status' => '2', 'message' => 'Price Basis cannot be empty.']);
                return;
            }
            if (empty($data['order_delivery_period'])) {
                echo json_encode(['status' => '2', 'message' => 'Delivery Period cannot be empty.']);
                return;
            }
            //$users_id          =   $this->session->userdata('auth_user')['users_id'];
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $users_id  =   $users['parent_id'];
                // $user_parrent_id=$users['parent_id'];
            } else {
                $users_id   =  $users['users_id'];
            }
            $qry_buyer_details = $this->db->select('organisation_short_code')
                               ->get_where('buyer_details', array('user_id' => $users_id));
            $organisation_short_code='';
            if ($qry_buyer_details->num_rows()==1) {

                foreach ($qry_buyer_details->result() as $buyer_detail) {
                    $organisation_short_code = $buyer_detail->organisation_short_code;
                }
            }
            // $no= $this->db->distinct()->select('manual_po_number')->from('tbl_manual_po_order')->where('buyer_user_id',$this->session->userdata('auth_user')['users_id'])->count_all_results();
            $no= $this->db->distinct()->select('manual_po_number')->from('tbl_manual_po_order')->where('user_parrent_id',$users_id)->count_all_results();
            // for ($i = 0; $i < count($data['manual_po_inventory']); $i++) {
            foreach ($data['manual_po_inventory'] as $i=>$value) {
                $insertData = [
                    'manual_po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1)),
                    'inventory_id' => intval($data['manual_po_inventory'][$i]),
                    'product_id' => intval($data['product_id'][$i]),
                    'product_quantity' => floatval($data['product_qtys'][$i]),
                    'product_price' => floatval($data['product_rate'][$i]),
                    'product_total_amount' => round($data['product_total_amount'][$i],2),
                    'product_gst' => floatval($data['product_gst'][$i]),
                    'order_status' => 1,
                    'buyer_user_id' => $this->session->userdata('auth_user')['users_id'],
                    'user_parrent_id'=>$users_id,
                    'prepared_by' => $this->session->userdata('auth_user')['users_id'],
                    'approved_by' => $this->session->userdata('auth_user')['users_id'],
                    'vendor_id' => intval($data['vendor_id']),
                    'order_add_remarks' => $data['order_add_remarks'],
                    'order_remarks' => $data['order_remarks'],
                    'order_delivery_period' => intval($data['order_delivery_period']),
                    'order_price_basis ' => $data['order_price_basis'],
                    'order_payment_term' => $data['order_payment_term'],
                ];
                //pr($insertData); die;
                // Insert into the database
                $this->db->insert('tbl_manual_po_order', $insertData);
            }

            $order_confirmation = $this->db->get_where('tbl_manual_po_order', array('manual_po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1))))->row();
            $last_inserted_id = $order_confirmation->id;
            $notification_data['order_no'] = 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1));
            $notification_data['id'] = $last_inserted_id;
            $notification_data['rfq_no'] = '';
            $notification_data['user_id'] = get_vendor_user_id(intval($data['vendor_id']));
            $notification_data['message_type'] = 'manual_order_confirmed';
            $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($this->session->userdata('auth_user')['users_id']);
            $status = send_notification_to_vendor($notification_data);
            $this->sendOrderConfirmationMail(['seller_user_id' => $notification_data['user_id'], 'rfq_number' => '', 'po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1)), 'order_date' => date("d/m/Y", strtotime($order_confirmation->created_at)), 'vendor_currency' => $buyer_currency ]);
            $this->db->trans_complete();


            $response = [
                'status' => '1',
                'message' => 'Manual PO generated successfully!'
            ];
            echo json_encode($response);
            return;
        }

        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }
    public function generate_manual_possss(){
        if ($this->input->is_ajax_request()) {
            // Get the posted data
            $data = $this->input->post();
            if (empty($data['vendor_id'])) {
                echo json_encode(['status' => '2', 'message' => 'Vendor ID cannot be empty.']);
                return;
            }
            if (empty($data['order_payment_term'])) {
                echo json_encode(['status' => '2', 'message' => 'Payment Term cannot be empty.']);
                return;
            }
            if (empty($data['order_price_basis'])) {
                echo json_encode(['status' => '2', 'message' => 'Price Basis cannot be empty.']);
                return;
            }
            if (empty($data['order_delivery_period'])) {
                echo json_encode(['status' => '2', 'message' => 'Delivery Period cannot be empty.']);
                return;
            }
            //$users_id          =   $this->session->userdata('auth_user')['users_id'];
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $users_id  =   $users['parent_id'];
            } else {
                $users_id   =  $users['users_id'];
            }
            $qry_buyer_details = $this->db->select('organisation_short_code')
                               ->get_where('buyer_details', array('user_id' => $users_id));
            $organisation_short_code='';
            if ($qry_buyer_details->num_rows()==1) {

                foreach ($qry_buyer_details->result() as $buyer_detail) {
                    $organisation_short_code = $buyer_detail->organisation_short_code;
                }
            }
            $no= $this->db->distinct()->select('manual_po_number')->from('tbl_manual_po_order')->where('buyer_user_id',$this->session->userdata('auth_user')['users_id'])->count_all_results();
            for ($i = 0; $i < count($data['manual_po_inventory']); $i++) {
                $insertData = [
                    'manual_po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1)),
                    'inventory_id' => intval($data['manual_po_inventory'][$i]),
                    'product_id' => intval($data['product_id'][$i]),
                    'product_quantity' => floatval($data['product_qtys'][$i]),
                    'product_price' => floatval($data['product_rate'][$i]),
                    'product_total_amount' => ($data['product_total_amount'][$i]),
                    'product_gst' => floatval($data['product_gst'][$i]),
                    'order_status' => 1,
                    'buyer_user_id' => $this->session->userdata('auth_user')['users_id'],
                    'prepared_by' => $this->session->userdata('auth_user')['users_id'],
                    'approved_by' => $this->session->userdata('auth_user')['users_id'],
                    'vendor_id' => intval($data['vendor_id']),
                    'order_add_remarks' => $data['order_add_remarks'],
                    'order_remarks' => $data['order_remarks'],
                    'order_delivery_period' => intval($data['order_delivery_period']),
                    'order_price_basis ' => $data['order_price_basis'],
                    'order_payment_term' => $data['order_payment_term'],
                ];

                // Insert into the database
                $this->db->insert('tbl_manual_po_order', $insertData);
            }

            $order_confirmation = $this->db->get_where('tbl_manual_po_order', array('manual_po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1))))->row();
            $last_inserted_id = $order_confirmation->id;
            $notification_data['order_no'] = 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1));
            $notification_data['id'] = $last_inserted_id;
            $notification_data['rfq_no'] = '';
            $notification_data['user_id'] = get_vendor_user_id(intval($data['vendor_id']));
            $notification_data['message_type'] = 'manual_order_confirmed';
            $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($this->session->userdata('auth_user')['users_id']);
            $status = send_notification_to_vendor($notification_data);
             $this->sendOrderConfirmationMail(['seller_user_id' => $notification_data['user_id'], 'rfq_number' => '', 'po_number' => 'MO-' .$organisation_short_code .'-'. date('y') . '-' .  sprintf('%03d',($no+1)), 'order_date' => date("d/m/Y", strtotime($order_confirmation->created_at)), 'vendor_currency' => $buyer_currency ]);
            $this->db->trans_complete();


            $response = [
                'status' => '1',
                'message' => 'Manual PO generated successfully!'
            ];
            echo json_encode($response);
            return;
        }

        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }
    public function sendOrderConfirmationMail($data) {
        $rfq_no = $data['rfq_number'];
        $vendor_user_id = $data['seller_user_id'];
        $po_number = $data['po_number'];
        $order_date = $data['order_date'];
        $vendor_currency = $data['vendor_currency'];
        $vendor_data = get_vendor_data_by_user_id($vendor_user_id);
        $vendor_store_name = $vendor_data->store_name;
        $vendor_email = $vendor_data->email_address;
        $auth_user = $this->session->userdata('auth_user');
        $buyer_data = getBuyerName($auth_user['users_id']);
        $user_email = $auth_user['email_address'];
        $buyer_name = !empty($buyer_data) ? $buyer_data->legal_name : $auth_user['first_name'];
        $subject = "Manual Order Confirmed (Order No. " . $po_number . " )";
        $dispatch_address = '';
        $delivery_address = '';
        $product_data = $this->getPoProductDetails($po_number, get_currency_symbol_to_str($vendor_currency));
        $mail_data = getSystemEmail('order-confirmation-email');
        $admin_msg = $mail_data[0]->content;
        $admin_msg = str_replace_vendor_email_data($admin_msg);
        $admin_msg = str_replace('$rfq_date_formate', $order_date, $admin_msg);
        $admin_msg = str_replace('$rfq_number', $rfq_no, $admin_msg);
        $admin_msg = str_replace('$buyer_name', $buyer_name, $admin_msg);
        $admin_msg = str_replace('$vendor_name', $vendor_store_name, $admin_msg);
        $admin_msg = str_replace('$product_details', $product_data, $admin_msg);
        $admin_msg = str_replace('$dispatch_address', $dispatch_address, $admin_msg);
        $admin_msg = str_replace('$delivery_address', $delivery_address, $admin_msg);
        $admin_msg = str_replace('$order_id', $po_number, $admin_msg);
        $admin_msg = str_replace('$order_date', $order_date, $admin_msg);
        $admin_msg = str_replace('$website_url', base_url(), $admin_msg);
        SendEmail_helper($subject, $admin_msg, $vendor_email);
    }
    public function getPoProductDetails($po_number, $vendor_currency) {

        $this->db->select('mpo.product_quantity, mpo.product_price, mpo.product_gst, tpm.prod_name, inv.uom');
        $this->db->from('tbl_product_master tpm, tbl_manual_po_order mpo');
        $this->db->join('inventory_mgt inv', 'mpo.inventory_id = inv.id', 'left');
        $this->db->where('mpo.manual_po_number', $po_number);
        $this->db->where('tpm.prod_id = mpo.product_id');
        // $this->db->where('a.record_type', 2);
        $this->db->where('mpo.order_status', 1);
        $product_data = $this->db->get()->result();
        $p_html = '';
        $total_price = 0;
        if (!empty($product_data)) {
            $uom_arr = array();
            foreach (getUOMList() as $key => $value) {
                $uom_arr[$value->id] = $value->uom_name;
            }
            foreach ($product_data as $key => $value) {
                $sub_total_price = $value->product_price * $value->product_quantity;
                /*if ($value->vend_product_gst != '') {
                    $sub_total_price = $sub_total_price + ($sub_total_price * $value->vend_product_gst / 100);
                }*/
                $total_price+= $sub_total_price;
                $product_total_amount = number_format((float)$sub_total_price, 2, '.', '');
                $po_total_amout = number_format((float)$total_price, 2, '.', '');
                $p_html.= '<tr class="td_class">
                                <td class="td_class">
                                  ' . $value->prod_name . '
                                </td>
                                <td class="td_class" style="text-align: center;">
                                  ' . $value->product_quantity . '
                                </td>
                                <td class="td_class">
                                  '. $uom_arr[$value->uom] .'
                                </td>
                                <td class="td_class">
                                ' . $vendor_currency .' ' .IND_money_format($product_total_amount) . '
                                </td>
                            </tr>';
            }
            $p_html.= '<tr>
                            <td colspan="3" class="td_class">Total</td>
                            <td class="td_class">
                            ' . $vendor_currency .' ' .IND_money_format($po_total_amout) . '
                            </td>
                        </tr>';
        }
        return $p_html;
    }
    public function add_inventory_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            //$all_stores             =   $this->get_store_details();
            $product_ids            =   $this->input->post('product_ids');
            $product_name           =   $this->input->post('product_name');
            $status                 =   $this->input->post('status');
            $division_id            =   $this->input->post('division_id');
            $category_id            =   $this->input->post('category_id');
            $product_specification  =   _sanetiz_all_string_data($this->input->post('product_specification'),'encode');
            $product_size           =   _sanetiz_all_string_data($this->input->post('product_size'),'encode');
            $product_stock          =   $this->input->post('product_stock');
            $stock_price            =   $this->input->post('stock_price');
            $product_uom            =   $this->input->post('product_uom');
            $branch_id              =   $this->input->post('branch_id');
            $location_id            =   $this->input->post('buyer_location');
            $inventory_grouping     =   $this->input->post('inventory_grouping');
            $buyer_product_name     =   $this->input->post('buyer_product_name');
            $inventory_type         =   $this->input->post('inventory_type');
            $indent_min_qty         =   $this->input->post('indent_min_qty');
            $product_brand          =   $this->input->post('product_brand');
            if(isset($location_id) && isset($product_name) && !empty($product_name) && isset($division_id) && !empty($division_id) && isset($category_id) && !empty($category_id) && isset($product_uom) && !empty($product_uom) && ((isset($product_stock) && !empty($product_stock)) || ($product_stock=='0'))){
                if(isset($status) && $status==1){
                    $max_inv_id =   1;
                    $vrify_qry = $this->db->select_max("comp_br_sp_inv_id")->get_where('inventory_mgt',array('company_id' => $company_id, 'branch_id' => $branch_id));
                    if($vrify_qry->num_rows()){
                        $row_data   =   $vrify_qry->row();
                        $max_inv_id =   ($row_data->comp_br_sp_inv_id)+1;
                    }
                    $ins['comp_br_sp_inv_id']   =   $max_inv_id;
                    $ins['company_id']          =   $company_id;
                    $ins['branch_id']           =   $branch_id;
                    $ins['product_id']          =   $product_ids;
                    $ins['product_name']        =   $product_name;
                    $ins['specification']       =   substr($product_specification,0,2900);
                    $ins['size']                =   substr($product_size,0,1450);
                    $ins['opening_stock']       =   $product_stock;
                    $ins['stock_price']         =   $stock_price;
                    $ins['location_id ']         =  $location_id;
                    $ins['uom']                 =   $product_uom;
                    $ins['inventory_grouping']  =   $inventory_grouping;
                    $ins['buyer_product_name']  =   $buyer_product_name;
                    $ins['inventory_type']      =   isset($inventory_type) && $inventory_type!="" ? $inventory_type : 0;
                    $ins['indent_min_qty']      =   $indent_min_qty;
                    $ins['product_brand']       =   $product_brand;
                    $ins['added_by']            =   $users['users_id'];
                    $ins['created_date']        =   date('Y-m-d H:i:s');
                    $where = [];
                    $where['specification']     =  $product_specification;
                    $where['size']              =  $product_size;
                    $where['product_id']        =  $product_ids;
                   // $where['product_name']      =  $product_name;
                    $where['company_id']        =  $company_id;
                    $where['branch_id']         =  $branch_id;
                    $get_old_inventory = $this->db->select('id')->where($where)->get('inventory_mgt');
                    if($get_old_inventory->num_rows() < 1){
                        $qry    =   $this->db->insert('inventory_mgt',$ins);
                        if($qry){
                            $res['status']          =   1;
                            $res['message']         =   'Inventory added successfully';
                            echo json_encode($res); die;
                        }
                        else{
                            $res['status']          =   2;
                            $res['message']         =   'Inventory not added, please try again';
                            echo json_encode($res); die;
                        }
                    }else{
                        $res['status']          =   2;
                        $res['message']         =   'Please Add Unique  Size Or Specification With this Product';
                        echo json_encode($res); die;
                    }
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Invalid Product';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'All field are required';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function pending_grn_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Pending GRN Report Management";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $this->load->view('inventory_management/pending_grn_report_list',$data);
    }

    public function get_pending_grn_report_data()
    {

        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id    =   $this->session->userdata('auth_user')['users_id'];
        $users      =   $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record   =   $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'total', $cat_id);
        // pr($result); die;
        $po_numbers     =   array();
        $po_by_id       =   array();
        $price_by_po    =   array();
        $get_grn_type   =   array();
        $with_po_price  =   array();
        $vendor_detail  =   array();
        $invarrs        = array();
        $new_stock_return_for = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $invarrs[$v->inventory_id]=$v->inventory_id;
                if($v->grn_type == 1){
                    $po_numbers[]       =   $v->po_number;
                    $po_by_id[$v->id]   =   $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id]  =   $v->rate;
                }
                elseif($v->grn_type == 3){
                    if($v->stock_return_for != 0){
                        $new_stock_return_for[$v->stock_return_for] = $v->stock_return_for;
                    }
                }
                $get_grn_type[$v->id]   =   $v->grn_type;
            }
            if(isset($new_stock_return_for) && !empty($new_stock_return_for)){
                $this->db->where_in('id',$new_stock_return_for);
                $new_st_grn_qry = $this->db->select('id,grn_type,po_number,rate')->get_where('grn_mgt',array());
                if($new_st_grn_qry->num_rows()){
                    foreach($new_st_grn_qry->result() as $stgrnqry){
                        if($stgrnqry->grn_type == 1){
                            if(!in_array($stgrnqry->po_number,$po_numbers)){
                                $po_numbers[]               =   $stgrnqry->po_number;
                            }
                            $po_by_id[$stgrnqry->id]    =   $stgrnqry->po_number;

                        }elseif($stgrnqry->grn_type == 2){
                            $with_po_price[$stgrnqry->id]  =   $stgrnqry->rate;
                        }
                        $get_grn_type[$stgrnqry->id]   =   $stgrnqry->grn_type;
                    }
                }
            }
            ///pr($po_by_id); die;
            if(isset($po_numbers) && !empty($po_numbers)){
                $response_price_by_po_vendor_detail        =   $this->get_price_by_po_vendor_detail($po_numbers);
                $price_by_po                               =    $response_price_by_po_vendor_detail['price_by_po'];
                $vendor_detail                             =    $response_closed_order_final_close_order['vendor_detail'];


            }
        }
        //pr($price_by_po); die;
        // pr($vendor_detail);die;
        // pr($price_by_po[$po_by_id[1]]);die;

        $totindqty = array();
        $no_inven_data = [];

        $sr_no = 1;
        $data1 = array();
        //pr($result); die;
        foreach ($result as $key => $val) {
            if($val->order_quantity==$val->total_grn_qty || $val->order_quantity<$val->total_grn_qty){
                continue;
            }
            $sub_array = array();
            //listing------
            $sub_array[] = $sr_no;
            $sub_array[] = $val->po_number;
            $sub_array[] = date("d/m/Y", strtotime($val->order_date));
            $sub_array[] = $val->prod_name;
            //$sub_array[] = $val->div_name;
            //$sub_array[] = $val->cat_name;
            if($val->grn_type==1){
                $sub_array[] = isset($vendor_detail[$val->po_number][$val->inventory_id]) ? $vendor_detail[$val->po_number][$val->inventory_id] : '';
            }
            else{
                $sub_array[] = $val->vendor_name;
            }
            $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';;
            $sub_array[] = strlen($val->size)<=20 ? $val->size.$is_del_invs : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[] = $val->inventory_grouping;
            $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $sub_array[] = date("d/m/Y", strtotime($val->last_updated_date));
            $sub_array[] = $val->uom_name;
            $sub_array[] = round($val->order_quantity,2);
            $sub_array[] = round($val->total_grn_qty,2);
            $finalqtys   = round($val->order_quantity,2)-round($val->total_grn_qty,2);
            $sub_array[] = round($finalqtys,2);

            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function get_price_by_po_vendor_detail($po_numbers) {
        if (empty($po_numbers)) {
            return ['price_by_po' => [], 'vendor_detail' => []];
        }

        $this->db->where_in('po_number', $po_numbers);
        $this->db->select("tro.id, tro.price_id, tro.po_number, tro.order_quantity, tro.updated_at, tro.order_price, tr.vend_id, ts.store_name, tr.inventory_id", false);
        $this->db->from("tbl_rfq_order as tro");
        $this->db->join("tbl_rfq_price as trp", 'trp.id = tro.price_id', 'LEFT');
        $this->db->join("tbl_rfq as tr", 'tr.id = trp.rfq_record_id', 'LEFT');
        $this->db->join("tbl_store as ts", 'ts.store_id = tr.vend_id');

        $qry_rfq_placeorder = $this->db->get();

        $price_by_po = [];
        $vendor_detail = [];

        if ($qry_rfq_placeorder->num_rows() > 0) {
            foreach ($qry_rfq_placeorder->result() as $po_n) {
                $price_by_po[$po_n->po_number][$po_n->inventory_id] = $po_n->order_price;
                $vendor_detail[$po_n->po_number][$po_n->inventory_id] = $po_n->store_name;
            }
        }

        return [
            'price_by_po' => $price_by_po,
            'vendor_detail' => $vendor_detail
        ];
    }

    public function export_pending_grn_report_old()
    {

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'page');
        // print_r($result);
        $total_record = $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'total');
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $div_arrs = array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',', $vals->category_ids);
            $div_arrs[$dcat_arr['0']] = $dcat_arr['0'];
            $div_arrs[$dcat_arr['1']] = $dcat_arr['1'];
        }
            $po_numbers = array();
        $po_by_id = array();
        $price_by_po = array();
        $get_grn_type = array();
        $with_po_price = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                if($v->grn_type == 1){
                    $po_numbers[] = $v->po_number;
                    $po_by_id[$v->id] = $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id] = $v->rate;
                }
                $get_grn_type[$v->id] = $v->grn_type;
            }
            if(isset($po_numbers) && !empty($po_numbers)){
                $this->db->where_in('po_number',$po_numbers);
                $qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number] = $po_n->order_price;
                    }
                }
            }
        }
        $final_data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            if($val->order_quantity==$val->total_grn_qty || $val->order_quantity<$val->total_grn_qty){
                continue;
            }
            // $expdivcat = explode(',',$val->category_ids);
            // $alldivcatnames = getCategorySubCategoryName_smt($div_arrs);
            // $finldivcat['division_name']=isset($alldivcatnames[$expdivcat['0']]) ? $alldivcatnames[$expdivcat['0']] : '';
            // $finldivcat['category_name']=isset($alldivcatnames[$expdivcat['1']]) ? $alldivcatnames[$expdivcat['1']] : '';
            $sub_array = array();
            //listing------
            $final_data[$i]['Serial Number'] = $i+1;
            $final_data[$i]['Order Number'] = $val->po_number;
            $final_data[$i]['Order Date'] = date("d/m/Y", strtotime($val->order_date));
            $final_data[$i]['Product Name'] = $val->prod_name;
            $final_data[$i]['Vender Name'] = $val->vendor_name;
            $final_data[$i]['Specification'] = $val->specification;
            $final_data[$i]['Size'] = $val->size;
            $final_data[$i]['UOM'] = $val->uom_name;
            $final_data[$i]['Inventory Grouping'] = $val->inventory_grouping;
            $final_data[$i]['Added BY'] =  $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date'] =date("d/m/Y", strtotime($val->last_updated_date));
            $final_data[$i]['Order Quantity'] = $val->order_quantity;
            $final_data[$i]['GRN Quantity'] = $val->total_grn_qty;
            $final_data[$i]['Pending GRN Quantity'] = $val->order_quantity-$val->total_grn_qty;



            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function export_pending_grn_report()
    {
        $cat_id = array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'page');
        // print_r($result);
        $total_record = $this->inventory_management_model->get_pending_grn_report_data($users_ids, $buyer_users, 'total');
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $div_arrs = array();
        $vendor_detail  =   array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',', $vals->category_ids);
            $div_arrs[$dcat_arr['0']] = $dcat_arr['0'];
            $div_arrs[$dcat_arr['1']] = $dcat_arr['1'];
        }
        $po_numbers = array();
        $po_by_id = array();
        $price_by_po = array();
        $get_grn_type = array();
        $with_po_price = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                if($v->grn_type == 1||$v->grn_type == 4){
                    $po_numbers[] = $v->po_number;
                    $po_by_id[$v->id] = $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id] = $v->rate;
                }
                $get_grn_type[$v->id] = $v->grn_type;
            }
            // if(isset($po_numbers) && !empty($po_numbers)){
            //     $this->db->where_in('po_number',$po_numbers);
            //     $qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
            //     if($qry_rfq_placeorder->num_rows()){
            //         foreach($qry_rfq_placeorder->result() as $po_n){
            //             $price_by_po[$po_n->po_number] = $po_n->order_price;
            //             $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
            //         }
            //     }
            // }
            if(isset($po_numbers) && !empty($po_numbers)){
                // die('test');
                $this->db->where_in('po_number',$po_numbers);
                //$this->db->where_in('tr.inventory_id',$invarrs);
                //$qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
                $this->db->select("tro.id,tro.price_id,tro.po_number,tro.order_quantity,tro.updated_at,tro.order_price,tr.vend_id,ts.store_name,tr.inventory_id", false);
                $this->db->from("tbl_rfq_order as tro");
                $this->db->join("tbl_rfq_price as trp", 'trp.id=tro.price_id', 'LEFT');
                $this->db->join("tbl_rfq as tr", 'tr.id=trp.rfq_record_id', 'LEFT');
                $this->db->join("tbl_store as ts",'ts.store_id=tr.vend_id');
                $qry_rfq_placeorder = $this->db->get();
                // pr($qry_rfq_placeorder->result()); die;
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }

            }
        }
        $final_data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            if($val->order_quantity==$val->total_grn_qty || $val->order_quantity<$val->total_grn_qty){
                continue;
            }
            // $expdivcat = explode(',',$val->category_ids);
            // $alldivcatnames = getCategorySubCategoryName_smt($div_arrs);
            // $finldivcat['division_name']=isset($alldivcatnames[$expdivcat['0']]) ? $alldivcatnames[$expdivcat['0']] : '';
            // $finldivcat['category_name']=isset($alldivcatnames[$expdivcat['1']]) ? $alldivcatnames[$expdivcat['1']] : '';
            $sub_array = array();
            //listing------
            $final_data[$i]['Serial Number']    =   $i+1;
            $final_data[$i]['Order Number']     =   $val->po_number;
            $final_data[$i]['Order Date']       =   date("d/m/Y", strtotime($val->order_date));
            $final_data[$i]['Product Name']     =   $val->prod_name;
            if($val->grn_type==1){
                $final_data[$i]['Vender Name']  ='j'+ isset($vendor_detail[$val->po_number][$val->inventory_id]) ? $vendor_detail[$val->po_number][$val->inventory_id] : '5';
            }
            else{
                $final_data[$i]['Vender Name']  =   $val->vendor_name;
            }

            // $final_data[$i]['Vender Name'] = $val->vendor_name;
            $final_data[$i]['Specification']        =   ($val->specification);
            $final_data[$i]['Size']                 =   ($val->size);
            $final_data[$i]['Inventory Grouping']   =   ($val->inventory_grouping);
            $final_data[$i]['Added BY']             =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date']           =   date("d/m/Y", strtotime($val->last_updated_date));
            $final_data[$i]['UOM']                  =   $val->uom_name;
            $final_data[$i]['Order Quantity']       =   round($val->order_quantity,2);
            $final_data[$i]['GRN Quantity']         =   round($val->total_grn_qty,2);
            $final_data[$i]['Pending GRN Quantity'] =   round($val->order_quantity,2)-round($val->total_grn_qty,2);



            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function update_inventory_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            //$all_stores             =   $this->get_store_details();
            $inventory_form_up_id   =   $this->input->post('inventory_form_up_id');
            $product_ids            =   $this->input->post('product_ids');
            $product_name           =   $this->input->post('product_name');
            $status                 =   $this->input->post('status');
            $division_id            =   $this->input->post('division_id');
            $category_id            =   $this->input->post('category_id');
            $product_specification  =   _sanetiz_all_string_data($this->input->post('product_specification'),'encode');
            $product_size           =   _sanetiz_all_string_data($this->input->post('product_size'),'encode');
            $product_stock          =   $this->input->post('product_stock');
            $stock_price            =   $this->input->post('stock_price');
            $product_uom            =   $this->input->post('product_uom');
            $branch_id              =   $this->input->post('branch_id');
            $inventory_grouping     =   $this->input->post('inventory_grouping');
            $buyer_product_name     =   $this->input->post('buyer_product_name');
            $inventory_type         =   $this->input->post('inventory_type');
            $indent_min_qty         =   $this->input->post('indent_min_qty');
            $indent_min_qty         =   $this->input->post('indent_min_qty');
            $product_brand          =   $this->input->post('product_brand');
            if(isset($product_name) && !empty($product_name) && isset($inventory_form_up_id) && !empty($inventory_form_up_id) && isset($product_uom) && !empty($product_uom) && ((isset($product_stock) && !empty($product_stock)) || ($product_stock=='0'))){
                if(isset($inventory_form_up_id) && $inventory_form_up_id!=""){
                    $ins['product_id']          =   $product_ids;
                    $ins['product_name']        =   $product_name;
                    $ins['specification']       =   substr($product_specification,0,2900);
                    $ins['size']                =   substr($product_size,0,1450);
                    $ins['opening_stock']       =   $product_stock;
                    $ins['stock_price']         =   $stock_price;
                    $ins['uom']                 =   $product_uom;
                    $ins['indent_min_qty']      =   $indent_min_qty;
                    $ins['inventory_grouping']  =   $inventory_grouping;
                    $ins['buyer_product_name']  =   $buyer_product_name;
                    $ins['inventory_type']      =   isset($inventory_type) && $inventory_type!="" ? $inventory_type : 0;
                    $ins['product_brand']       =   $product_brand;
                    $ins['updated_by']          =   $users['users_id'];
                    $ins['updated_at']          =   date('Y-m-d H:i:s');
                    $where = [];
                    $where['specification']     =  $product_specification;
                    $where['size']              =  $product_size;
                    $where['product_id']        =  $product_ids;
                    //$where['product_name']      =  $product_name;
                    $where['company_id']        =  $company_id;
                    $where['branch_id']         =  $branch_id;
                    $this->db->where('id !=',$inventory_form_up_id);
                    $get_old_inventory = $this->db->select('id')->where($where)->get('inventory_mgt');
                    if($get_old_inventory->num_rows() < 1){
                        $this->db->where('id',$inventory_form_up_id);
                        $qry    =   $this->db->update('inventory_mgt',$ins);
                        if($qry){
                            $res['status']          =   1;
                            $res['message']         =   'Inventory added successfully';
                            echo json_encode($res); die;
                        }
                        else{
                            $res['status']          =   2;
                            $res['message']         =   'Inventory not added, please try again';
                            echo json_encode($res); die;
                        }
                    }else{
                        $res['status']          =   2;
                        $res['message']         =   'Please Add Unique  Size Or Specification With this Product';
                        echo json_encode($res); die;
                    }
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Invalid Product';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'All field are required';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function get_inventory(){
        if($this->input->is_ajax_request()){
            $inventory  =   $this->input->post('inventory',true);
            if($inventory){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $data   =   $query->row();
                    $resp    =   array();
                    $resp['inv_id']              =   $data->id;
                    $resp['comp_br_sp_inv_id']   =   $data->comp_br_sp_inv_id;
                    $resp['product_id']          =   $data->product_id;
                    $resp['specification']       =   $data->specification;
                    $resp['size']                =   $data->size;
                    $resp['uom']                 =   $data->uom;
                    $resp['product_name']        =   $data->prod_name;
                    $resp['uom_name']            =   $data->uom_name;

                    $res['status']              =   1;
                    $res['message']             =   'Inventory found';
                    $res['data']                =   $resp;
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }

    public function add_indent_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $is_active          =   2;
                $company_id  =   $users['parent_id'];
            } else {
                $is_active          =   1;
                $company_id         =  $users['users_id'];
            }
            $branch_id              =   $this->input->post('branch_id');
            $inventory              =   $this->input->post('inventory');
            $product_qty            =   $this->input->post('product_qty');
            $indent_remarks         =   $this->input->post('indent_remarks');
            if(isset($branch_id) && !empty($branch_id) && isset($inventory) && !empty($inventory) && isset($product_qty) && !empty($product_qty) ){
                $max_ind_id =   1;
                //$vrify_qry = $this->db->select_max("comp_br_sp_inv_id")->get_where('inventory_mgt',array('company_id' => $company_id, 'branch_id' => $branch_id));
                //$vrify_qry = $this->db->select_max("comp_br_sp_ind_id")->get_where('indent_mgt',array('inventory_id' => $inventory));
                $vrify_qry = $this->db->select_max("comp_br_sp_ind_id")->get_where('indent_mgt',array('company_id' => $company_id));
                if($vrify_qry->num_rows()){
                    $row_data   =   $vrify_qry->row();
                    $max_ind_id =   ($row_data->comp_br_sp_ind_id)+1;
                }
                $ins['company_id']          =   $company_id;
                $ins['comp_br_sp_ind_id']   =   $max_ind_id;
                $ins['inventory_id']        =   $inventory;
                $ins['indent_qty']          =   $product_qty;
                $ins['is_active']           =   $is_active;
                $ins['remarks']             =   $indent_remarks;
                $ins['created_by']          =   $users['users_id'];
                $ins['last_updated_by']     =   $users['users_id'];
                $ins['last_updated_date']   =   date('Y-m-d H:i:s');
                $qry    =   $this->db->insert('indent_mgt',$ins);
                if($qry){
                    $this->db->where('id',$inventory);
                    $this->db->update('inventory_mgt',array('is_indent' => 1));
                    $res['status']          =   1;
                    $res['is_active']       =   $is_active;
                    $res['message']         =   'Indent added successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Indent not added, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Wrong Indent data';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function fetch_all_indent_data(){
        if($this->input->is_ajax_request()){
            $inventory            =   $this->input->post('inventory');
            if($inventory){
                //$qry = $this->db->get_where('indent_mgt', array('inventory_id' => $inventory));
                $this->db->select("ind.id,ind.last_updated_date,ind.inventory_id,ind.comp_br_sp_ind_id,ind.indent_qty,ind.grn_qty,ind.remarks,ind.last_updated_by,ind.created_by,ind.is_active,tusr.first_name,tusr.last_name,tusr2.first_name as Addfname,tusr2.last_name as Addedlname", false);
                $this->db->from("indent_mgt as ind");
                $this->db->join("tbl_users as tusr",'tusr.id=ind.last_updated_by', 'LEFT');
                $this->db->join("tbl_users as tusr2",'tusr2.id=ind.created_by', 'LEFT');
                $this->db->where('ind.inventory_id',$inventory);
                $this->db->where('ind.inv_status',1);
                $this->db->where('ind.is_deleted !=',1);
                $query = $this->db->get();
                if($query->num_rows()){
                    $res['status']          =   1;
                    $res['message']         =   'Indent  found';
                    $res['resp']            =   $query->result();
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Indent not found';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Indent not found';
            echo json_encode($res); die;
        }
    }

    public function edit_indent_data(){
        if($this->input->is_ajax_request()){
            $indent            =   $this->input->post('indent');
            if($indent){
                $this->db->select("ind.id,ind.inventory_id,ind.comp_br_sp_ind_id,ind.indent_qty,ind.remarks,ind.last_updated_by,ind.is_active,tusr.first_name,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name,tusr.last_name", false);
                $this->db->from("indent_mgt as ind");
                $this->db->join("inventory_mgt as inv",'inv.id=ind.inventory_id', 'LEFT');
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->join("tbl_users as tusr",'tusr.id=ind.last_updated_by', 'LEFT');
                $this->db->where('ind.id',$indent);
                $query = $this->db->get();
                if($query->num_rows()){
                    $result_data            =   $query->row();
                    $inventory_id           =   $result_data->inventory_id;
                    $comp_br_sp_ind_id      =   $result_data->comp_br_sp_ind_id;
                    //$tot_qty                =   $result_data->indent_qty;
                    $tot_qty                =   0;
                    $this->db->select("SUM(ind.indent_qty) AS tot_inv_qty");
                    $this->db->from("indent_mgt as ind");
                    $this->db->where('ind.inventory_id',$inventory_id);
                    $this->db->where('ind.inv_status',1);
                    $this->db->where('ind.is_deleted',0);
                    $this->db->where('ind.comp_br_sp_ind_id <',$comp_br_sp_ind_id);
                    $this->db->group_by('ind.inventory_id');
                    $totqry = $this->db->get();
                    if($totqry->num_rows()){
                        //$tot_qty       =   $tot_qty+$totqry->row()->tot_inv_qty;
                        $tot_qty       =   $totqry->row()->tot_inv_qty;
                    }
                    $users          =   $this->session->userdata('auth_user');
                    if($users['parent_id'] != '') {
                        $is_active          =   2;
                    } else {
                        $is_active          =   1;
                    }
                    $res['tot_qty']         =   $tot_qty;
                    $res['status']          =   1;
                    $res['is_active']       =   $is_active;
                    $res['message']         =   'Indent  found';
                    $res['data']            =   $result_data;
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Indent not found';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Indent not found';
            echo json_encode($res); die;
        }
    }

    public function update_indent(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $is_parent      =   0;
                $company_id     =   $users['parent_id'];
            } else {
                //$upd['is_active']   =   1;
                $is_parent          =   1;
                $company_id   =  $users['users_id'];
            }
            $branch_id            =   $this->input->post('branch_id');
            $indent_id            =   $this->input->post('indent_id');
            $product_qty          =   $this->input->post('product_qty');
            $indent_remarks       =   $this->input->post('indent_remarks');
            if(isset($branch_id) && !empty($branch_id) && isset($indent_id) && !empty($indent_id) && isset($product_qty) && !empty($product_qty) ){

                $upd['indent_qty']          =   $product_qty;
                $upd['remarks']             =   $indent_remarks;
                $upd['last_updated_by']     =   $users['users_id'];
                $upd['last_updated_date']   =   date('Y-m-d H:i:s');
                $this->db->where('id',$indent_id);
                $qry    =   $this->db->update('indent_mgt',$upd);
                if($qry){
                    $res['status']          =   1;
                    $res['is_parent']       =   0;
                    $res['message']         =   'Indent update successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Indent not added, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Wrong Indent data';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function approve_indent_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $is_parent      =   0;
                $company_id     =   $users['parent_id'];
            } else {
                //$upd['is_active']   =   1;
                $is_parent          =   1;
                $company_id   =  $users['users_id'];
            }
            $branch_id            =   $this->input->post('branch_id');
            $indent_id            =   $this->input->post('indent_id');
            $product_qty          =   $this->input->post('product_qty');
            $indent_remarks       =   $this->input->post('indent_remarks');
            if(isset($branch_id) && !empty($branch_id) && isset($indent_id) && !empty($indent_id) && isset($product_qty) && !empty($product_qty) ){

                $upd['indent_qty']          =   $product_qty;
                $upd['remarks']             =   $indent_remarks;
                $upd['is_active']           =   1;
                $upd['last_updated_by']     =   $users['users_id'];
                $upd['last_updated_date']   =   date('Y-m-d H:i:s');
                $this->db->where('id',$indent_id);
                $qry    =   $this->db->update('indent_mgt',$upd);
                if($qry){
                    $res['status']          =   1;
                    $res['is_parent']       =   1;
                    $res['message']         =   'Indent update successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Indent not added, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Wrong inventory data';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function delete_indent(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            $branch_id            =   $this->input->post('branch_id');
            $indent_id            =   $this->input->post('indent_id');
            if(isset($branch_id) && !empty($branch_id) && isset($indent_id) && !empty($indent_id)){
                $this->db->where('id',$indent_id);
                $qry    =   $this->db->update('indent_mgt',array('is_deleted' => '1'));
                if($qry){
                    $inv_qry = $this->db->select('inventory_id')->get_where('indent_mgt',array('id' => $indent_id));
                    if($inv_qry->num_rows()){
                        $inventory_id = $inv_qry->row()->inventory_id;
                        $nxt_ind_qry = $this->db->get_where('indent_mgt',array('inventory_id' => $inventory_id, 'is_deleted' => '0'));
                        if($nxt_ind_qry->num_rows()<=0){
                            $this->db->where('id',$inventory_id);
                            $this->db->update('inventory_mgt',array('is_indent' => '0'));
                        }
                    }
                    $res['is_active']          =   0;
                    $isactive_qry = $this->db->select('is_active')->get_where('indent_mgt',array('id' => $indent_id, 'is_active' => '1'));
                    if($isactive_qry->num_rows()){
                        $res['is_active']          =   1;
                    }
                    $res['status']          =   1;
                    $res['message']         =   'Indent deleted successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Indent not deleted, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Wrong Indent data';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Error';
            echo json_encode($res); die;
        }
    }

    public function get_inventry_data_for_rfq(){
        if($this->input->is_ajax_request()){
            $inventories    =   $this->input->post('inventories');
            $total_invents  =   count($inventories);
            if($inventories){
                //$this->db->select("inv.*,tp.prod_name,tu.uom_name,tusr.first_name,tusr.last_name,SUM(ind.indent_qty) AS total_quantity", false);
                $this->db->select("MAX(`inv`.`id`) AS id,MAX(`inv`.`comp_br_sp_inv_id`) AS comp_br_sp_inv_id,MAX(`inv`.`company_id`) AS company_id,MAX(`inv`.`branch_id`) AS branch_id,MAX(`inv`.`location_id`) AS location_id,MAX(`inv`.`product_id`) AS product_id,MAX(`inv`.`buyer_product_name`) AS buyer_product_name,MAX(`inv`.`specification`) AS specification,MAX(`inv`.`size`) AS size,MAX(`inv`.`opening_stock`) AS opening_stock,MAX(`inv`.`stock_price`) AS stock_price,MAX(`inv`.`uom`) AS uom,MAX(`tp`.`prod_name`) AS prod_name,MAX(`tu`.`uom_name`) AS uom_name,MAX(`tusr`.`first_name`) AS first_name,MAX(`tusr`.`last_name`) AS last_name,SUM(ind.indent_qty) AS total_quantity", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->join("tbl_users as tusr",'tusr.id=inv.added_by', 'LEFT');
                $this->db->join("indent_mgt as ind",'ind.inventory_id=inv.id');
                $this->db->where_in('inv.id',$inventories);
                $this->db->where('ind.is_deleted','0');
                $this->db->where('ind.inv_status','1');
                $this->db->where('ind.is_active',1);
                $this->db->group_by("ind.inventory_id");
                $this->db->order_by('tp.prod_name');
                $query = $this->db->get();
                if($query->num_rows()){
                    $first_arr =$query->result();
                    $in_res = count($query->result());
                    if($total_invents == $in_res){
                        $res['status']          =   1;
                        $res['message']         =   'Indent  found';
                        $res['resp']            =   $query->result();
                        $res['new_inv_res']     =  [];
                    }else{
                        $invent_id = [];
                        $no_invent_id = [];
                        foreach($query->result() as $val){
                            $invent_id[]=$val->id;
                        }
                        foreach($inventories as $id){
                           if(!in_array($id,$invent_id)){
                               $no_invent_id[] = $id;
                           }
                        }
                        //$this->db->select("inv.*,tp.prod_name,tu.uom_name,tusr.first_name,tusr.last_name", false);
                        $this->db->select("MAX(`inv`.`id`) AS id,MAX(`inv`.`comp_br_sp_inv_id`) AS comp_br_sp_inv_id,MAX(`inv`.`company_id`) AS company_id,MAX(`inv`.`branch_id`) AS branch_id,MAX(`inv`.`location_id`) AS location_id,MAX(`inv`.`product_id`) AS product_id,MAX(`inv`.`buyer_product_name`) AS buyer_product_name,MAX(`inv`.`specification`) AS specification,MAX(`inv`.`size`) AS size,MAX(`inv`.`opening_stock`) AS opening_stock,MAX(`inv`.`stock_price`) AS stock_price,MAX(`inv`.`uom`) AS uom,MAX(`tp`.`prod_name`) AS prod_name,MAX(`tu`.`uom_name`) AS uom_name,MAX(`tusr`.`first_name`) AS first_name,MAX(`tusr`.`last_name`) AS last_name", false);
                        $this->db->from("inventory_mgt as inv");
                        $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                        $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                        $this->db->join("tbl_users as tusr",'tusr.id=inv.added_by', 'LEFT');
                        $this->db->where_in('inv.id',$no_invent_id);
                        $this->db->group_by("inv.id");
                        $this->db->order_by('tp.prod_name');
                        $query_1 = $this->db->get();
                        $new_inves_ar = [];
                        if($query_1->num_rows()){
                            $new_inves_ar = $query_1->result();
                        }
                        $final_array = array_merge($first_arr,$new_inves_ar);
                        $res['status']          =   1;
                        $res['message']         =   'Indent  found';
                        $res['resp']            =   $final_array;
                        $res['new_inv_res']     =   $no_invent_id;
                    }
                    //====TOTAL RFQ===//
                    $rfq_qty            =   array();
                    $close_rfq_id_arr   =   array();
                    $rfq_ids_against_inventory_id   =   array();
                    $this->db->group_by('variant_grp_id');
                    $this->db->where_in('inventory_id',$inventories);
                    $rfq_qry = $this->db->select('MAX(`id`) AS id,MAX(`rfq_id`) AS rfq_id,MAX(`inventory_id`) AS inventory_id,MAX(`quantity`) AS quantity,MAX(`buyer_rfq_status`) AS buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                    if($rfq_qry->num_rows()){
                        foreach($rfq_qry->result() as $rfq_rows){
                            if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                                $close_rfq_id_arr[$rfq_rows->id]=$rfq_rows->id;
                                $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                            }else{
                                $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                            }
                        }
                    }
                    //====TOTAL RFQ===//
                    //===Closed RFQ Qty=====//
                    $close_price_ids    =   array();
                    $closed_order       =   array();
                    $final_close_order  =   array();
                    $get_inv_ids_price  =   array();
                    if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                        $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                        $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                        if($close_qry_rfq_price->num_rows()){
                            foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                                $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                                $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                            }
                        }
                    }
                    if(isset($close_price_ids) && !empty($close_price_ids)){
                        $this->db->where_in('price_id',$close_price_ids);
                        $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                        if($qry_rfq_order->num_rows()){
                            foreach($qry_rfq_order->result() as $rfq_ord){
                                $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                            }
                            foreach($closed_order as $crows_key => $crow_val){
                                $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                            }
                        }
                    }
                    //===Closed RFQ Qty=====//
                    $res['orders']          =   $rfq_qty;
                    $res['close_orders']    =   $final_close_order;
                    echo json_encode($res); die;
                }else{
                    $this->db->select("MAX(`inv`.`id`) AS id,MAX(`inv`.`comp_br_sp_inv_id`) AS comp_br_sp_inv_id,MAX(`inv`.`company_id`) AS company_id,MAX(`inv`.`branch_id`) AS branch_id,MAX(`inv`.`location_id`) AS location_id,MAX(`inv`.`product_id`) AS product_id,MAX(`inv`.`buyer_product_name`) AS buyer_product_name,MAX(`inv`.`specification`) AS specification,MAX(`inv`.`size`) AS size,MAX(`inv`.`opening_stock`) AS opening_stock,MAX(`inv`.`stock_price`) AS stock_price,MAX(`inv`.`uom`) AS uom,MAX(`tp`.`prod_name`) AS prod_name,MAX(`tu`.`uom_name`) AS uom_name,MAX(`tusr`.`first_name`) AS first_name,MAX(`tusr`.`last_name`) AS last_name", false);
                    //$this->db->select("inv.*,tp.product_name,tu.uom_name,tusr.first_name,tusr.last_name", false);
                    $this->db->from("inventory_mgt as inv");
                    $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                    $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                    $this->db->join("tbl_users as tusr",'tusr.id=inv.added_by', 'LEFT');
                    $this->db->where_in('inv.id',$inventories);
                    $this->db->group_by("inv.id");
                    $this->db->order_by('tp.prod_name');
                    $query_1 = $this->db->get();
                    $new_inves_ar = [];
                    if($query_1->num_rows()){
                        $new_inves_ar = $query_1->result();
                    }
                    $res['status']          =   1;
                    $res['message']         =   'Indent  found';
                    $res['resp']            =   $new_inves_ar;
                    $res['new_inv_res']     =   $inventories;
                    $res['orders']=[];
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Indent not found';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Indent not found';
            echo json_encode($res); die;
        }
    }

    public function inventory_rfq(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            //$inventory_product  =   $this->input->post('inventory_product');
            $rfq_inventory          =   $this->input->post('rfq_inventory');
            $product_qty_rfq        =   $this->input->post('product_qty_rfq');
            $qty_counter=array();
            foreach($rfq_inventory as $rfq_key => $rfq_val){
                $qty_counter[$rfq_val] = $product_qty_rfq[$rfq_key];
            }
            $this->db->select("inv.*,tp.prod_name,tp.cat_id", false);
            $this->db->from("inventory_mgt as inv");
            $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
            $this->db->where_in('inv.id',$rfq_inventory);
            $query = $this->db->get();
            if($query->num_rows()){
                $ind_remarks = array();
                $this->db->where_in('inventory_id',$rfq_inventory);
                $invqry = $this->db->select('id,comp_br_sp_ind_id,inventory_id,remarks')->get_where('indent_mgt',array('inv_status' => 1, 'is_deleted' => '0','is_active' => 1));
                if($invqry->num_rows()){
                    $inv_pnr = '';
                    foreach($invqry->result() as $inrows){
                        if($inv_pnr == ''){
                            $inv_pnr = $inrows->comp_br_sp_ind_id;
                        }
                        else{
                            $inv_pnr .= ','.$inrows->comp_br_sp_ind_id;
                        }
                        $ind_remarks[$inrows->inventory_id][]=$inrows->remarks;
                    }
                    $_POST['p_prn_number']= $inv_pnr;
                }
                $product_name_arr       =    array();
                $i=0;
                foreach ($query->result() as $key => $vals) {
                    //====final list===//
                    //if(!in_array($vals->product_id, $product_name_arr)){
                        $data_product                   =   [];
                        $data_product['product_name']   =   $vals->prod_name;
                        $data_product['vendors_ids']    =   '';
                        $data_product['brand']          =   $vals->product_brand;
                        $data_product['remarks']        =   isset($ind_remarks[$vals->id]) ? implode(',', $ind_remarks[$vals->id]) : '';
                        $data_product['specification']  =   $vals->specification;
                        $data_product['size']           =   $vals->size;
                        $data_product['quantity']       =   isset($qty_counter[$vals->id]) ? $qty_counter[$vals->id] : 1;
                        $data_product['uom']            =   $vals->uom;
                        $all_product_data[]             =   $data_product;
                        $product_name_arr[]             =   $vals->product_id;
                    //}
                    //====final list===//
                    $i++;
                }
                //pr($all_product_data); die;
                $saveToDraft                =   $this->saveToDraft($all_product_data);
                if($saveToDraft){
                    $response['rfq_id']         =   $saveToDraft;
                    $response['status']         =   1;
                    //$response['all_product_data']=  $all_product_data;
                    $response['message']        =   'Inventry RFQ created successfully.';
                    echo json_encode($response); die;
                }
                else{
                    $response['status']         =   0;
                    $response['message']        =   'Inventry Data Not Found';
                    echo json_encode($response); die;
                }
            }
        }
    }
    function saveToDraft($all_product_data){
        $rfq_draft_id_tme       =   time();
        $rfq_draft_id_rnd       =   rand(10000, 99999);
        $prn_no                 =   $this->input->post('p_prn_number');
        $buyer_branch           =   $this->input->post('branch_id');
        $last_resp_dt           =   $this->input->post('last_date_to_response');
        $rfq_id                 =   'D' . $rfq_draft_id_tme . $rfq_draft_id_rnd;
        $userdata               =   $this->session->userdata('auth_user');
        $buyer_user_id          =   $userdata['users_id'];
        $buyer_id               =   getBuyerParentIdBySession();
        if(count($all_product_data)>0){
            $p_sr = 0;
            foreach ($all_product_data as $key => $value) {
                $product_name   =   $value['product_name'];
                $variant_grp_id =   time() . rand(10000, 99999);
                $this->db->select('prod_id');
                $this->db->from('tbl_product_master');
                $this->db->where('prod_name', $product_name);
                $query  =   $this->db->get();
                if ($query->num_rows()) {
                    $master_prod_id = $query->row()->prod_id;
                    $insert_data = array(
                        'rfq_id'            =>  $rfq_id,
                        'buyer_id'          =>  $buyer_id,
                        'vend_id'           =>  '',
                        'buyer_user_id'     =>  $buyer_user_id,
                        'record_type'       =>  1,
                        'prn_no'            =>  $prn_no,
                        'buyer_branch'      =>  $buyer_branch,
                        'last_resp_dt'      =>  $last_resp_dt,
                        'master_prod_id'    =>  $master_prod_id,
                        'variant_grp_id'    =>  $variant_grp_id,
                        'brand'             =>  $value['brand'],
                        'buyer_remarks'     =>  $value['remarks'],
                        'specification'     =>  $value['specification'],
                        'size'              =>  $value['size'],
                        'quantity'          =>  $value['quantity'],
                        'uom'               =>  $value['uom'],
                        'rfq_record_id'     =>  0,
                        'vend_id'           =>  0,
                        'is_bulk_rfq'       =>  2,
                        'created_at'        =>  date('Y-m-d H:i:s')
                    );
                    // Insert data into tbl_rfq
                    $this->db->insert('tbl_rfq', $insert_data);
                    $last_id    =   $this->db->insert_id();
                    $this->db->where('id', $last_id);
                    $this->db->update('tbl_rfq', array('rfq_record_id' => $last_id));
                    $p_sr++;
                }
            }
        }
        if($p_sr>0){
            return $rfq_id;
        }
        else{
            return false;
        }
    }

    public function active_rfq_details(){
        $response = [];
        if(isset($_POST) && !empty($_POST)){
            $this->db->group_by('rfq_id');
            $qry = $this->db->select('MAX(rfq_id) as rfq_id,MAX(quantity) as quantity,MAX(updated_at) as updated_at,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inventory_id' => $_POST['inven_id']));
            if($qry->num_rows()){
                $response['closed_rfq'] =   $closed_order;
                $response['status']     =   '1';
                $response['data']       =   $qry->result();
                $response['message']    =   'RFQ Active Details Succesfully Fetched Against This Inventory';
            }
            else{
                $response['data']       =   [];
                $response['status']     =   '0';
                $response['message']    =   'SomeThing Went Wrong';
            }
        }
        echo json_encode($response); die;
    }

    public function get_order_details(){
        if($this->input->is_ajax_request()){
            $order_details              =   array();
            $rfq_recod_id               =   array();
            $price_details              =   array();
            $price_data                 =   array();
            $order_price_ids            =   array();
            $inventory_id   =   $this->input->post('inven_id');
            $qry = $this->db->select('id,rfq_id')->get_where('tbl_rfq',array('record_type' => '2', 'inventory_id' => $_POST['inven_id']));
            if($qry->num_rows()){
                foreach($qry->result() as $rows){
                   $rfq_recod_id[]              =   $rows->id;
                   $price_details[$rows->id]    =   $rows->rfq_id;
                }
                $this->db->select("trp.id,trp.rfq_record_id,ts.store_name", false);
                $this->db->from("tbl_rfq_price as trp");
                $this->db->join("tbl_users as tu",'tu.id=trp.vend_user_id');
                $this->db->join("tbl_store as ts",'tu.store_id=ts.store_id');
                $this->db->where_in('trp.rfq_record_id',$rfq_recod_id);
                $ord_qry_rfq_price = $this->db->get();
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $price_data[$rfq_prc_row->id]['rfq_id']         =   isset($price_details[$rfq_prc_row->rfq_record_id]) ? $price_details[$rfq_prc_row->rfq_record_id] : '';
                        $price_data[$rfq_prc_row->id]['vendor_name']    =   $rfq_prc_row->store_name;
                    }
                }
                if(isset($order_price_ids) && !empty($order_price_ids)){
                    $this->db->group_by('po_number');
                    $this->db->where_in('price_id',$order_price_ids);
                    $qry_rfq_placeorder = $this->db->select('MAX(id) as id,MAX(price_id) as price_id,MAX(po_number) as po_number,MAX(order_quantity) as order_quantity,MAX(updated_at) as updated_at')->get_where('tbl_rfq_order',array('order_status' => 1));
                    if($qry_rfq_placeorder->num_rows()){
                        $i=0;
                        foreach($qry_rfq_placeorder->result() as $po_row){
                            $order_details[$i]['po_id']             =   $po_row->id;
                            $order_details[$i]['po_number']         =   $po_row->po_number;
                            $order_details[$i]['rfq_number']        =   isset($price_data[$po_row->price_id]['rfq_id']) ? $price_data[$po_row->price_id]['rfq_id'] :'';
                            $order_details[$i]['order_date']        =   $po_row->updated_at;
                            $order_details[$i]['order_quantity']    =   $po_row->order_quantity;
                            $order_details[$i]['vendor_name']       =   isset($price_data[$po_row->price_id]['vendor_name']) ? $price_data[$po_row->price_id]['vendor_name'] :'';
                            $i++;
                        }
                    }
                }
            }
            if(isset($order_details) && !empty($order_details)){
                $response['status']     =   '1';
                $response['data']       =   $order_details;
                $response['message']    =   'Order Details Succesfully Fetched Against selected Inventory';
            }else{
              $response['data']         =   [];
              $response['status']       =   '0';
              $response['message']      =   'No Order details Found';
            }
            echo json_encode($response); die;
        }
    }

    public function get_grn_data_details(){
        if($this->input->is_ajax_request()){
            $order_details              =   array();
            $rfq_recod_id               =   array();
            $price_details              =   array();
            $price_data                 =   array();
            $order_price_ids            =   array();
            $po_ids                     =   array();
            $grn_ord_details            =   array();
            $inventory_id   =   $this->input->post('inven_id');
            $qry = $this->db->select('id,rfq_id')->get_where('tbl_rfq',array('record_type' => '2', 'inventory_id' => $_POST['inven_id']));
            if($qry->num_rows()){
                foreach($qry->result() as $rows){
                   $rfq_recod_id[]              =   $rows->id;
                   $price_details[$rows->id]    =   $rows->rfq_id;
                }
                $this->db->select("trp.id,trp.rfq_record_id,ts.store_name", false);
                $this->db->from("tbl_rfq_price as trp");
                $this->db->join("tbl_users as tu",'tu.id=trp.vend_user_id');
                $this->db->join("tbl_store as ts",'tu.store_id=ts.store_id');
                $this->db->where_in('trp.rfq_record_id',$rfq_recod_id);
                $ord_qry_rfq_price = $this->db->get();
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $price_data[$rfq_prc_row->id]['rfq_id']         =   isset($price_details[$rfq_prc_row->rfq_record_id]) ? $price_details[$rfq_prc_row->rfq_record_id] : '';
                        $price_data[$rfq_prc_row->id]['vendor_name']    =   $rfq_prc_row->store_name;
                    }
                }
                if(isset($order_price_ids) && !empty($order_price_ids)){
                    $this->db->where_in('price_id',$order_price_ids);
                    $qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price,vend_currency')->get_where('tbl_rfq_order',array('order_status' => 1));
                    if($qry_rfq_placeorder->num_rows()){
                        $i=0;
                        foreach($qry_rfq_placeorder->result() as $po_row){
                            $po_ids[$po_row->id]                    =   $po_row->id;
                            $order_details[$i]['po_id']             =   $po_row->id;
                            $order_details[$i]['po_number']         =   $po_row->po_number;
                            $order_details[$i]['rfq_number']        =   isset($price_data[$po_row->price_id]['rfq_id']) ? $price_data[$po_row->price_id]['rfq_id'] :'';
                            $order_details[$i]['order_date']        =   date('Y-m-d',strtotime($po_row->updated_at));
                            $order_details[$i]['order_quantity']    =   $po_row->order_quantity;
                            $order_details[$i]['rate']              =   $po_row->order_price;
                            $order_details[$i]['vendor_name']       =   isset($price_data[$po_row->price_id]['vendor_name']) ? $price_data[$po_row->price_id]['vendor_name'] :'';
                            $order_details[$i]['vend_currency']     =   $po_row->vend_currency;
                            $i++;
                        }
                    }
                }
            }
            if(isset($po_ids) && !empty($po_ids)){
                $this->db->group_by('order_id');
                $this->db->group_by('inventory_id');
                $this->db->where_in('order_id',$po_ids);
                $grn_qry = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(approved_by) as approved_by,MAX(grn_buyer_rate) as grn_buyer_rate')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted !=' => '1'));
                if($grn_qry->num_rows()){
                    foreach($grn_qry->result() as $grn_res){
                        $grn_ord_details[$grn_res->order_id]['qty']             =   $grn_res->total_grn_quantity;
                        $grn_ord_details[$grn_res->order_id]['approved_by']     =   $grn_res->approved_by;
                        $grn_ord_details[$grn_res->order_id]['grn_buyer_rate']  =   $grn_res->grn_buyer_rate;
                    }
                }
            }
            //===Repairable stock return===//
            $repair_stock       =   array();
            $grn_stock_details  =   array();
            $stock_rep_qry = $this->db->get_where('tbl_return_stock',array('inventory_id' => $inventory_id, 'stock_return_type' => '1', 'is_deleted' => '0'));
            if($stock_rep_qry->num_rows()){
                $repair_stock = $stock_rep_qry->result();
                $stock_ids_arr  =   array();
                // foreach($repair_stock as $rprow){
                //     $stock_ids_arr[$rprow->id]  =   $rprow->id;
                // }
                foreach($repair_stock as $rprow=>$value){
                    //  $stock_ids_arr[$rprow->id]  =   $rprow->id;
                     $stock_ids_arr[$repair_stock[$rprow]->id]  =   $repair_stock[$rprow]->id;
                    $repair_stock[$rprow]->last_updated_date=date('Y-m-d',strtotime($repair_stock[$rprow]->last_updated_date));
                    // echo date('Y-m-d',strtotime($rprow->last_updated_date));
                    // print_r($repair_stock[$rprow]->id);
                }
                $this->db->group_by('inventory_id');
                $this->db->group_by('stock_id');
                $this->db->where_in('stock_id',$stock_ids_arr);
                $grn_stk_qry = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(stock_id) as stock_id,MAX(inventory_id) as inventory_id,MAX(approved_by) as approved_by')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted !=' => '1'));
                if($grn_stk_qry->num_rows()){
                    foreach($grn_stk_qry->result() as $grn_stk_res){
                        $grn_stock_details[$grn_stk_res->stock_id]['qty']             =   $grn_stk_res->total_grn_quantity;
                        $grn_stock_details[$grn_stk_res->stock_id]['approved_by']     =   $grn_stk_res->approved_by;
                    }
                }
            }
            //===Repairable stock return===//
            if(isset($order_details) && !empty($order_details)){
                $response['status']             =   '1';
                $response['data']               =   $order_details;
                $response['grn_data']           =   $grn_ord_details;
                $response['repair_stock']       =   $repair_stock;
                $response['grn_stock_details']  =   $grn_stock_details;
                $response['message']            =   'Order Details Succesfully Fetched Against selected Inventory';
            }else{
                $response['status']             =   '1';
                $response['data']               =   array();
                $response['grn_data']           =   $grn_ord_details;
                $response['repair_stock']       =   $repair_stock;
                $response['grn_stock_details']  =   $grn_stock_details;
                $response['message']            =   'Order Details Succesfully Fetched Against selected Inventory';
            //   $response['data']         =   [];
            //   $response['status']       =   '0';
            //   $response['message']      =   'No Order details Found';
            }
            $manual_qry = $this->db
                ->select('tbl_manual_po_order.id, manual_po_number, updated_at, product_quantity, product_price,ts.store_name,inventory_id')
                ->from('tbl_manual_po_order')
                ->join('tbl_store as ts', 'ts.store_id = tbl_manual_po_order.vendor_id')
                ->where('order_status', '1')
                ->where('inventory_id', $_POST['inven_id'])
                ->get();

            $manual_order_details = array();
            if ($manual_qry->num_rows() > 0) { // Fixed condition check
                $i = 0;
                $total_grn_quantity = 0;
                foreach ($manual_qry->result() as $manual_po_row) {
                    $grn_manual_qry = $this->db->select(' SUM(grn_qty) AS total_grn_quantity ')->from('grn_mgt')
                    ->where(array(
                        'inventory_id' => $manual_po_row->inventory_id,
                        'order_id' => $manual_po_row->id,
                        'grn_type' => 4,
                        'is_deleted !=' => 1 // Make sure 'is_deleted' is not deleted
                    ))
                    ->get();

                    if ($grn_manual_qry->num_rows() > 0) {
                        $result = $grn_manual_qry->row();
                        $total_grn_quantity = $result->total_grn_quantity;
                    } else {
                        $total_grn_quantity = 0; // Handle the case where no records are found
                    }
                    if($total_grn_quantity==$manual_po_row->product_quantity){
                        continue;
                    }

                    $manual_order_details[$i]['po_id']             = $manual_po_row->id;
                    $manual_order_details[$i]['po_number']         = $manual_po_row->manual_po_number;
                    $manual_order_details[$i]['total_grn_quantity']= $total_grn_quantity==null?0: round($total_grn_quantity,2);
                    $manual_order_details[$i]['rfq_number']        = ''; // Placeholder for RFQ number
                    $manual_order_details[$i]['order_date']        = date('Y-m-d',strtotime($manual_po_row->updated_at));
                    $manual_order_details[$i]['order_quantity']    = $manual_po_row->product_quantity;
                    $manual_order_details[$i]['rate']              = $manual_po_row->product_price;
                    $manual_order_details[$i]['vendor_name']       = $manual_po_row->store_name;
                    $i++;
                }

            }
            $response['data_manual'] = $manual_order_details;
            echo json_encode($response); die;
        }
    }

    public function get_grn_data_details_old(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');
            // $final_arr_for_without_po = array();
            //  $qry = $this->db->select('*,SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt',array('inventory_id' => $inventory_id, 'is_deleted' => 0,'grn_type'=>2));
            //     if($qry->num_rows()){
            //         $row_data       =   $qry->result();
            //         //pr($row_data);
            //         foreach($row_data as $k1 => $vals){
            //             if($vals->grn_type == 2){
            //                 $final_arr_for_without_po[$k1]['approved_by'] = $vals->approved_by;
            //                 $final_arr_for_without_po[$k1]['grn_quantity'] = $vals->total_grn_quantity;
            //                 $final_arr_for_without_po[$k1]['first_name'] = $vals->vendor_name;
            //                 $final_arr_for_without_po[$k1]['created_at'] = $vals->last_updated_date;
            //                 $final_arr_for_without_po[$k1]['quantity'] = $vals->order_qty;
            //                 $final_arr_for_without_po[$k1]['po_number'] = $vals->order_no;
            //                 $final_arr_for_without_po[$k1]['grn_type'] = $vals->grn_type;
            //                 $final_arr_for_without_po[$k1]['rfq_number'] = $vals->rfq_no;

            //             }
            //         }
            //     }
            $this->db->where('tocd.inventory_id',$inventory_id);
            $this->db->select("tocd.vendor_id,tocd.order_confirmation_id,tocd.rfq_number,tocd.po_number,tocd.quantity,tocd.inventory_id,tocd.created_at,ts.store_name as first_name", false);
            $this->db->from("tbl_order_confirmation_details as tocd");
            //$this->db->join("tbl_users as tu",'tu.id=tocd.vendor_id');
            $this->db->join("tbl_store as ts",'ts.store_id=tocd.vendor_id');
            $this->db->where('tocd.order_status','1');
            $this->db->where('tocd.inv_status','1');
            $qry_ord_env = $this->db->get();
            //$qry_ord_env = $this->db->select('vendor_id,order_confirmation_id,rfq_number,po_number,quantity,inventory_id ,created_at')->get_where('tbl_order_confirmation_details',array('order_status' => '1'));
            //echo $this->db->last_query(); die;
            if($qry_ord_env->num_rows()){
                $ord_id_arr =   array();
                $store_arr  =   array();
                foreach($qry_ord_env->result() as $orw){
                    $ord_id_arr[$orw->order_confirmation_id]=$orw->order_confirmation_id;
                    $store_arr  =   $orw->store_id;
                }
                //=======GRN Qty===//
                $grn_ord_details    =   array();
                $this->db->where_in('order_id',$ord_id_arr);
                $this->db->where_in('is_deleted',0);
                $this->db->group_by('order_id');
                $this->db->group_by('inventory_id');
                $qry_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(approved_by) as approved_by')->get_where('grn_mgt',array());
                if($qry_grn_env->num_rows()){
                    foreach($qry_grn_env->result() as $grn_res){
                        $grn_ord_details[$grn_res->order_id][$grn_res->inventory_id]['qty']    =   $grn_res->total_grn_quantity;
                        $grn_ord_details[$grn_res->order_id][$grn_res->inventory_id]['approved_by']    =   $grn_res->approved_by;
                    }
                }
                //=======GRN Qty===//

                $ord_deatils=array();
                foreach($qry_ord_env->result() as $key => $ord_res){
                    $ord_deatils[$key]=$ord_res;
                    $ord_deatils[$key]->grn_quantity    =   isset($grn_ord_details[$ord_res->order_confirmation_id][$ord_res->inventory_id]['qty'] ) ? $grn_ord_details[$ord_res->order_confirmation_id][$ord_res->inventory_id]['qty']  : 0;
                    $ord_deatils[$key]->approved_by    =   isset($grn_ord_details[$ord_res->order_confirmation_id][$ord_res->inventory_id]['approved_by']) ? $grn_ord_details[$ord_res->order_confirmation_id][$ord_res->inventory_id]['approved_by'] : '';
                }
                // if(isset($final_arr_for_without_po) && !empty($final_arr_for_without_po)){

                //     $ord_deatils = array_merge($ord_deatils,$final_arr_for_without_po);
                // }
                //pr($ord_deatils);die;
                $response['status']     =   '1';
                $response['data']       =   $ord_deatils;
                $response['message']    =   'Order Details Succesfully Fetched Against selected Inventory';
            }else{
              $response['data']         =   [];
              $response['status']       =   '0';
              $response['message']      =   'No Order details Found';
            }
            echo json_encode($response);
        }
    }

    public function get_grn_edit_data_details(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');
            $orderid   =   $this->input->post('orderid');
            if($inventory_id && $orderid){
                $qry = $this->db->get_where('grn_mgt',array('inventory_id' => $inventory_id, 'is_deleted' => 0, 'inv_status' => '1', 'order_id' => $orderid));
                if($qry->num_rows()){
                    $order_id=array();
                    foreach($qry->result_array() as $rws){
                        $order_id[]=$rws['order_id'];
                    }
                    $this->db->where('tocd.inventory_id',$inventory_id);
                    $this->db->where_in('tocd.order_confirmation_id',$order_id);
                    $this->db->select("tocd.vendor_id,tocd.order_confirmation_id,tocd.rfq_number,tocd.po_number,tocd.quantity,tocd.inventory_id,tocd.created_at,ts.store_name as first_name", false);
                    $this->db->from("tbl_order_confirmation_details as tocd");
                    $this->db->join("tbl_store as ts",'ts.store_id=tocd.vendor_id');
                    $this->db->where('tocd.order_status','1');
                    $this->db->where('tocd.inv_status','1');
                    $qry_ord_env = $this->db->get();
                    $order_details  =   array();
                    if($qry_ord_env->num_rows()){
                        foreach($qry_ord_env->result_array() as $rows){
                            $order_details[$rows['order_confirmation_id']]=$rows;
                        }
                    }

                    $response['status']         =   '1';
                    $response['data']           =   $qry->result_array();
                    $response['order_details']  =   $order_details;
                    $response['message']        =   'GRN Fetched';
                }else{
                    $response['status']     =   '1';
                    $response['data']       =   array();
                    $response['message']    =   'GRN Not Found';
                }
            }else{
              $response['data']         =   array();
              $response['status']       =   '0';
              $response['message']      =   'Inventory details are not Found';
            }
            echo json_encode($response);
        }
    }
 public function get_grn_report_edit_data(){
        if($this->input->is_ajax_request()){
            $grn_id   =   $this->input->post('grn_id');
            if($grn_id){
                $qry = $this->db->get_where('grn_mgt',array('id' => $grn_id, 'is_deleted' => 0));
                if($qry->num_rows()){
                    $row_data       =   $qry->row();
                    if(isset($row_data->stock_return_for) && !empty($row_data->stock_return_for)){
                        $stock_for_ids = array();
                        $qry_all = $this->db->select('order_id')->get_where('grn_mgt',array('grn_type'=> 1,'is_deleted' => 0, 'id' => $row_data->stock_return_for));
                        if($qry_all->num_rows()){
                            $order_id       = $qry_all->row()->order_id;
                        }
                    }else{
                        $order_id       =   $row_data->order_id;
                    }
                    if( $row_data->grn_type==3){
                        $stock_id = $row_data->stock_id;
                        $stock_qry = $this->db->select('stock_no,qty,stock_vendor_name,last_updated_date')->get_where('tbl_return_stock',array('id' => $stock_id));
                        if($stock_qry->num_rows()){
                            $response['stock_data']       =   $stock_qry->row();
                        }
                    }
                    // pr($stock_for_ids);
                    // pr($order_id);die;
                    $inventory_id   =   $row_data->inventory_id;
                    $this->db->select('SUM(grn_qty) as total_quantity');
                    if(isset($row_data->grn_type) &&  $row_data->grn_type != 2){
                        $this->db->where('order_id', $order_id);
                    }
                    $this->db->where('inventory_id', $inventory_id);
                    $this->db->where('id !=', $grn_id);
                    $this->db->where('is_deleted', 0);
                    $qry2 = $this->db->get_where('grn_mgt');
                    $total_quantity = '';
                    $order_details  =   array();
                    if($qry2->num_rows()){

                        $total_quantity = $qry2->row()->total_quantity;
                    }
                    if(isset($row_data->grn_type) &&  $row_data->grn_type != 2 && $row_data->grn_type != 3){
                        // $this->db->where('tocd.inventory_id',$inventory_id);
                        // $this->db->where('to.id',$order_id);
                        // $this->db->select("to.po_number,to.id order_confirmation_id,to.order_quantity,to.created_at,tp.*,r.*,ts.*,ts.store_name as first_name", false);
                        // $this->db->from("tbl_rfq_order as to");
                        // $this->db->join('tbl_rfq_price as tp', 'tp.id = to.price_id');
                        // $this->db->join('tbl_rfq as r', 'r.id = tp.rfq_record_id');
                        // $this->db->join("tbl_store as ts",'ts.store_id=r.vend_id');
                        // // $this->db->where('tocd.order_status','1');
                        // $qry_ord_env = $this->db->get();

                        // if($qry_ord_env->num_rows()){
                        //     // pr($qry_ord_env->result_array());die;
                        //     foreach($qry_ord_env->result_array() as $rows){
                        //         $order_details[$rows['order_confirmation_id']]=$rows;
                        //     }
                        // }
                        if($row_data->grn_type==1){
                            // $this->db->where('tocd.inventory_id',$inventory_id);
                            $this->db->where('to.id',$order_id);
                            $this->db->select("to.po_number,to.id order_confirmation_id,to.order_quantity,to.created_at,tp.*,r.*,ts.*,ts.store_name as first_name", false);
                            $this->db->from("tbl_rfq_order as to");
                            $this->db->join('tbl_rfq_price as tp', 'tp.id = to.price_id');
                            $this->db->join('tbl_rfq as r', 'r.id = tp.rfq_record_id');
                            $this->db->join("tbl_store as ts",'ts.store_id=r.vend_id');
                            // $this->db->where('tocd.order_status','1');
                            $qry_ord_env = $this->db->get();

                            if($qry_ord_env->num_rows()){
                                // pr($qry_ord_env->result_array());die;
                                foreach($qry_ord_env->result_array() as $rows){
                                    $order_details[$rows['order_confirmation_id']]=$rows;
                                }
                            }
                        }
                        elseif($row_data->grn_type==4){
                            // $this->db->where('tocd.inventory_id',$inventory_id);
                            $this->db->where('mpo.id',$order_id);
                            $this->db->select("mpo.manual_po_number as po_number,mpo.id order_confirmation_id,mpo.product_quantity as order_quantity,mpo.created_at,ts.*,ts.store_name as first_name", false);
                            $this->db->from("tbl_manual_po_order as mpo");
                            $this->db->join("tbl_store as ts",'ts.store_id=mpo.vendor_id');
                            // $this->db->where('tocd.order_status','1');
                            $qry_ord_env = $this->db->get();

                            if($qry_ord_env->num_rows()){
                                // pr($qry_ord_env->result_array());die;
                                foreach($qry_ord_env->result_array() as $rows){
                                    $order_details[$rows['order_confirmation_id']]=$rows;
                                }
                            }
                        }
                    }elseif(isset($row_data->grn_type) &&  $row_data->grn_type == 3){
                        $qry2 = $this->db->get_where('grn_mgt',array('id' => $row_data->stock_return_for, 'is_deleted' => 0));
                        if($qry2->num_rows()){
                            $row_data2                          =   $qry2->row();
                            $order_details[0]['po_number']      =   $row_data2->order_no;
                            $order_details[0]['rfq_id']         =   $row_data2->rfq_no;
                            $order_details[0]['quantity']       =   $row_data2->order_qty;
                            $order_details[0]['inventory_id']   =   $row_data2->inventory_id;
                            $order_details[0]['created_at']     =   date('Y-m-d',strtotime($row_data2->last_updated_date));
                            $order_details[0]['first_name']     =   $row_data2->vendor_name;
                        }
                        else{
                            $order_details[0]['po_number']      =   'Opening Stock';
                            $order_details[0]['rfq_id']         =   'Opening Stock';
                            $order_details[0]['quantity']       =   $row_data->grn_qty;
                            $order_details[0]['inventory_id']   =   $row_data->inventory_id;
                            $order_details[0]['created_at']     =   date('Y-m-d',strtotime($row_data->last_updated_date));
                            $order_details[0]['first_name']     =   '';
                        }

                    }else{
                        $order_details[0]['po_number']= $row_data->order_no;
                        $order_details[0]['rfq_id']= $row_data->rfq_no;
                        $order_details[0]['quantity']= $row_data->order_qty;
                        $order_details[0]['inventory_id']= $row_data->inventory_id;
                        $order_details[0]['created_at']= date('Y-m-d',strtotime($row_data->last_updated_date));
                        $order_details[0]['first_name']= $row_data->vendor_name;
                    }
                    $edit_grn = 1;
                    $issue_qry = $this->db->select('id')->get_where('issued_mgt',array('issued_return_for' => $grn_id));
                    if($issue_qry->num_rows()){
                        $edit_grn = 2;
                    }
                    $stock_retn_qry = $this->db->select('id')->get_where('tbl_return_stock',array('stock_return_for' => $grn_id));
                    if($stock_retn_qry->num_rows()){
                        $edit_grn = 3;
                    }

                    $response['status']         =   '1';
                    $response['edit_grn']       =   $edit_grn;
                    $response['data']           =   $qry->result_array();
                    $response['total_quantity'] =   $total_quantity;
                    $response['order_details']  =   $order_details;
                    $response['order_id']  =   $order_id;
                    $response['message']        =   'GRN Fetched';
                }else{
                    $response['status']     =   '0';
                    $response['data']       =   array();
                    $response['message']    =   'GRN Not Found';
                }
            }else{
              $response['data']         =   array();
              $response['status']       =   '0';
              $response['message']      =   'Inventory details are not Found';
            }
            echo json_encode($response);
        }
    }
    public function get_grn_report_edit_data_old(){
        if($this->input->is_ajax_request()){
            $grn_id   =   $this->input->post('grn_id');
            if($grn_id){
                $qry = $this->db->get_where('grn_mgt',array('id' => $grn_id, 'is_deleted' => 0));
                if($qry->num_rows()){
                    $row_data       =   $qry->row();

                    $order_id       =   $row_data->order_id;
                    $inventory_id   =   $row_data->inventory_id;
                    $this->db->select('SUM(grn_qty) as total_quantity');
                    if(isset($row_data->grn_type) &&  $row_data->grn_type != 2){
                        $this->db->where('order_id', $order_id);
                    }
                    $this->db->where('inventory_id', $inventory_id);
                    $this->db->where('id !=', $grn_id);
                    $this->db->where('is_deleted', 0);
                    $qry2 = $this->db->get_where('grn_mgt');
                    $total_quantity = '';
                    $order_details  =   array();
                    if($qry2->num_rows()){

                        $total_quantity = $qry2->row()->total_quantity;
                    }
                    if(isset($row_data->grn_type) &&  $row_data->grn_type != 2){
                        $this->db->where('tocd.inventory_id',$inventory_id);
                        $this->db->where('tocd.order_confirmation_id',$order_id);
                        $this->db->select("tocd.vendor_id,tocd.order_confirmation_id,tocd.rfq_number,tocd.po_number,tocd.quantity,tocd.inventory_id,tocd.created_at,ts.store_name as first_name", false);
                        $this->db->from("tbl_order_confirmation_details as tocd");
                        $this->db->join("tbl_store as ts",'ts.store_id=tocd.vendor_id');
                        $this->db->where('tocd.order_status','1');
                        $qry_ord_env = $this->db->get();

                        if($qry_ord_env->num_rows()){
                            foreach($qry_ord_env->result_array() as $rows){
                                $order_details[$rows['order_confirmation_id']]=$rows;
                            }
                        }
                    }else{
                        $order_details[0]['po_number']= $row_data->order_no;
                        $order_details[0]['rfq_number']= $row_data->rfq_no;
                        $order_details[0]['quantity']= $row_data->order_qty;
                        $order_details[0]['inventory_id']= $row_data->inventory_id;
                        $order_details[0]['created_at']= $row_data->last_updated_date;
                        $order_details[0]['first_name']= $row_data->vendor_name;
                    }
                    $edit_grn = 1;
                    $issue_qry = $this->db->select('id')->get_where('issued_mgt',array('issued_return_for' => $grn_id));
                    if($issue_qry->num_rows()){
                        $edit_grn = 2;
                    }
                    $stock_retn_qry = $this->db->select('id')->get_where('tbl_return_stock',array('stock_return_for' => $grn_id));
                    if($stock_retn_qry->num_rows()){
                        $edit_grn = 3;
                    }

                    $response['status']         =   '1';
                    $response['edit_grn']       =   $edit_grn;
                    $response['data']           =   $qry->result_array();
                    $response['total_quantity'] =   $total_quantity;
                    $response['order_details']  =   $order_details;
                    $response['message']        =   'GRN Fetched';
                }else{
                    $response['status']     =   '0';
                    $response['data']       =   array();
                    $response['message']    =   'GRN Not Found';
                }
            }else{
              $response['data']         =   array();
              $response['status']       =   '0';
              $response['message']      =   'Inventory details are not Found';
            }
            echo json_encode($response);
        }
    }

    public function save_grn_qty(){
        $save_data = false;
        $mp_msg = '';
        if($this->input->is_ajax_request()){
            //pr($_POST); die;
            $users                      =   $this->session->userdata('auth_user');
            $grn_inventory_id           =   $this->input->post('grn_inventory_id');
            $grn_type_arr               =   $this->input->post('grn_type');
            $order_id_arr               =   $this->input->post('order_id');
            $order_no_arr               =   $this->input->post('order_no');
            $rfq_no_arr                 =   $this->input->post('rfq_no');
            $order_date_arr             =   $this->input->post('order_date');
            $order_qty_arr              =   $this->input->post('order_qty');
            $vendor_name_arr            =   $this->input->post('vendor_name');
            $grn_entered_arr            =   $this->input->post('grn_entered');
            $grn_buyer_rate_arr         =   $this->input->post('grn_buyer_rate');
            $grn_qty_arr                =   $this->input->post('grn_qty');
            $vendor_invoice_number_arr  =   $this->input->post('vendor_invoice_number');
            $vehicle_no_lr_no_arr       =   $this->input->post('vehicle_no_lr_no');
            $gross_wt_arr               =   $this->input->post('gross_wt');
            $gst_no_arr                 =   $this->input->post('gst_no');
            $frieght_other_charges_arr  =   $this->input->post('frieght_other_charges');
            $approved_by_arr            =   $this->input->post('approved_by');

            //manual data===//
            $grn_type_manual               =   $this->input->post('grn_type_manual');
            $order_no_manual               =   $this->input->post('order_no_manual');
            $order_id_manual               =   $this->input->post('order_id_manual');
            $rfq_no_manual                 =   $this->input->post('rfq_no_manual');
            $order_qty_manual              =   $this->input->post('order_qty_manual');
            $vendor_name_manual            =   $this->input->post('vendor_name_manual');
            $grn_rate_manual               =   $this->input->post('grn_rate_manual');
            $grn_qty_manual                =   $this->input->post('grn_qty_manual');
            $vendor_invoice_number_manual  =   $this->input->post('vendor_invoice_number_manual');
            $vehicle_no_lr_no_manual       =   $this->input->post('vehicle_no_lr_no_manual');
            $gross_wt_manual               =   $this->input->post('gross_wt_manual');
            $gst_no_manual                 =   $this->input->post('gst_no_manual');
            $frieght_other_charges_manual  =   $this->input->post('frieght_other_charges_manual');
            $approved_by_manual            =   $this->input->post('approved_by_manual');
            //manual data===//

            //wopo data===//
            $grn_type_wop               =   $this->input->post('grn_type_wop');
            $order_no_wop               =   $this->input->post('order_no_wop');
            $rfq_no_wop                 =   $this->input->post('rfq_no_wop');
            $order_qty_wop              =   $this->input->post('order_qty_wop');
            $vendor_name_wop            =   $this->input->post('vendor_name_wop');
            $grn_rate_wp                =   $this->input->post('grn_rate_wp');
            $grn_qty_wop                =   $this->input->post('grn_qty_wop');
            $vendor_invoice_number_wop  =   $this->input->post('vendor_invoice_number_wop');
            $vehicle_no_lr_no_wop       =   $this->input->post('vehicle_no_lr_no_wop');
            $gross_wt_wop               =   $this->input->post('gross_wt_wop');
            $gst_no_wop                 =   $this->input->post('gst_no_wop');
            $frieght_other_charges_wop  =   $this->input->post('frieght_other_charges_wop');
            $approved_by_wop            =   $this->input->post('approved_by_wop');
            //wopo data===//

            //====Stock Return Data==//
            $stock_return_id                =   $this->input->post('stock_return_id');
            $stock_return_for               =   $this->input->post('stock_return_for');
            $stock_grn_nos                  =   $this->input->post('stock_grn_nos');
            $stock_grn_qty                  =   $this->input->post('stock_grn_qty');
            $vendor_invoice_number_stock    =   $this->input->post('vendor_invoice_number_stock');
            $vehicle_no_lr_no_stock         =   $this->input->post('vehicle_no_lr_no_stock');
            $gross_wt_stock                 =   $this->input->post('gross_wt_stock');
            $gst_no_stock                   =   $this->input->post('gst_no_stock');
            $frieght_other_charges_stock    =   $this->input->post('frieght_other_charges_stock');
            $approved_by_stock              =   $this->input->post('approved_by_stock');
            //====Stock Return Data==//
            $i=0;
            $ins=array();
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            $grn_no     =   1;
            $vrify_qry = $this->db->select_max("grn_no")->get_where('grn_mgt',array('company_id' => $company_id));
            if($vrify_qry->num_rows()){
                $row_data               =   $vrify_qry->row();
                $grn_no                 =   ($row_data->grn_no)+1;
            }
            //with po ==//
            if(isset($grn_qty_arr) && !empty($grn_qty_arr)){
                $grn_qty = 0;
                foreach($grn_qty_arr as $grn_ky=>$grn_val){
                    if($grn_val!=""){
                        $ins[$i]['order_id']                =   $order_id_arr[$grn_ky];
                        $ins[$i]['po_number']               =   $order_no_arr[$grn_ky];
                        $ins[$i]['company_id']              =   $company_id;
                        $ins[$i]['grn_no']                  =   $grn_no;
                        $ins[$i]['grn_qty']                 =   $grn_val;
                        $ins[$i]['inventory_id']            =   $grn_inventory_id;
                        $ins[$i]['last_updated_by']         =   $users['users_id'];
                        $ins[$i]['approved_by']             =   $approved_by_arr[$grn_ky];
                        $ins[$i]['grn_type']                =   $grn_type_arr[$grn_ky];
                        $ins[$i]['grn_buyer_rate']          =   isset($grn_buyer_rate_arr[$grn_ky]) ? $grn_buyer_rate_arr[$grn_ky] : 0;
                        $ins[$i]['vendor_invoice_number']   =   $vendor_invoice_number_arr[$grn_ky];
                        $ins[$i]['vehicle_no_lr_no']        =   $vehicle_no_lr_no_arr[$grn_ky];
                        $ins[$i]['gross_wt']                =   $gross_wt_arr[$grn_ky];
                        $ins[$i]['gst_no']                  =   $gst_no_arr[$grn_ky];
                        $ins[$i]['frieght_other_charges']   =   $frieght_other_charges_arr[$grn_ky];
                        $ins[$i]['last_updated_date']       =   date('Y-m-d H:i:s');
                        $grn_qty                            =   $grn_qty+$grn_val;
                        $grn_no++;
                        $i++;
                    }
                }
                if(count($ins)>0){
                    $this->db->insert_batch('grn_mgt', $ins);
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                    $save_data = true;
                }
            }
            //with po==//
            //with manual po ==//
            if(isset($grn_qty_manual) && !empty($grn_qty_manual)){
                // var_dump($grn_qty_manual);die();
                $ins_manual=array();
                $ii=0;
                $manual_grn_qty = 0;
                foreach($grn_qty_manual as $grn_ky=>$grn_val){
                    if($grn_val!=""){
                        $mpo_available = $this->db->get_where('tbl_manual_po_order',array('order_status' => '2','id' => $order_id_manual[$grn_ky]));
                        if($mpo_available->num_rows()){
                            $response['status']     =   '2';
                            $response['message']    =   'This '.$order_no_manual[$grn_ky].' Order is already Cancelled.Please Refresh the Page.';
                            echo json_encode($response);die;
                        }


                        $ins_manual[$ii]['order_id']                =   $order_id_manual[$grn_ky];
                        $ins_manual[$ii]['po_number']               =   $order_no_manual[$grn_ky];
                        $ins_manual[$ii]['vendor_name']               =  $vendor_name_manual[$grn_ky];
                        $ins_manual[$ii]['company_id']              =   $company_id;
                        $ins_manual[$ii]['grn_no']                  =   $grn_no;
                        $ins_manual[$ii]['grn_qty']                 =   $grn_qty_manual[$grn_ky];
                        $ins_manual[$ii]['order_qty']               =  $order_qty_manual[$grn_ky];
                        $ins_manual[$ii]['inventory_id']            =   $grn_inventory_id;
                        $ins_manual[$ii]['last_updated_by']         =   $users['users_id'];
                        $ins_manual[$ii]['approved_by']             =   $approved_by_manual[$grn_ky];
                        $ins_manual[$ii]['grn_type']                =   $grn_type_manual[$grn_ky];
                        $ins_manual[$ii]['vendor_invoice_number']   =   $vendor_invoice_number_manual[$grn_ky];
                        $ins_manual[$ii]['vehicle_no_lr_no']        =   $vehicle_no_lr_no_manual[$grn_ky];
                        $ins_manual[$ii]['gross_wt']                =   $gross_wt_manual[$grn_ky];
                        $ins_manual[$ii]['gst_no']                  =   $gst_no_manual[$grn_ky];
                        $ins_manual[$ii]['frieght_other_charges']   =   $frieght_other_charges_manual[$grn_ky];
                        $ins_manual[$ii]['last_updated_date']       =   date('Y-m-d H:i:s');
                        $manual_grn_qty                            =   $manual_grn_qty+$grn_val;
                        $grn_no++;
                        $ii++;
                    }
                }

                if(count($ins_manual)>0){
                    $this->db->insert_batch('grn_mgt', $ins_manual);
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                    $save_data = true;
                }
            }
            //with manual po==//
            //with out po==//
            $inserted   =   array();
            if(isset($order_no_wop) && !empty($order_no_wop) && isset($rfq_no_wop) && !empty($rfq_no_wop) && isset($order_qty_wop) && !empty($order_qty_wop) && isset($grn_qty_wop) && !empty($grn_qty_wop)){
                $z = false;
                $ord_no_chk_qry = $this->db->get_where('grn_mgt',array('order_no' => $order_no_wop,'company_id' => $company_id));
                if($ord_no_chk_qry->num_rows()){
                    $z = true;
                }
                $rfq_no_chk_qry = $this->db->get_where('grn_mgt',array('rfq_no' => $rfq_no_wop,'company_id' => $company_id));
                if($rfq_no_chk_qry->num_rows()){
                    $z = true;
                }
                if(!$z){
                    $inserted['grn_qty']                    =   $grn_qty_wop;
                    $inserted['vendor_name']                =   $vendor_name_wop;
                    $inserted['order_no']                   =   $order_no_wop;
                    $inserted['rate']                       =   $grn_rate_wp;
                    $inserted['rfq_no']                     =   $rfq_no_wop;
                    $inserted['order_qty']                  =   $order_qty_wop;
                    $inserted['approved_by']                =   $approved_by_wop;
                    $inserted['grn_type']                   =   2;
                    $inserted['company_id']                 =   $company_id;
                    $inserted['grn_no']                     =   $grn_no;
                    $inserted['vendor_invoice_number']      =   $vendor_invoice_number_wop;
                    $inserted['vehicle_no_lr_no']           =   $vehicle_no_lr_no_wop;
                    $inserted['gross_wt']                   =   $gross_wt_wop;
                    $inserted['gst_no']                     =   $gst_no_wop;
                    $inserted['frieght_other_charges']      =   $frieght_other_charges_wop;
                    $inserted['inventory_id']               =   $grn_inventory_id;
                    $inserted['last_updated_by']            =   $users['users_id'];
                    $inserted['last_updated_date']          =   date('Y-m-d H:i:s');
                    $grninsqry = $this->db->insert('grn_mgt',$inserted);
                    if($grninsqry){
                        $grn_no++;
                        $response['status']     =   '1';
                        $response['message']    =   'GRN quantity updated successfully';
                        $save_data = true;
                    }
                }
                else{
                    $mp_msg =   'GRN will not update because Order No./RFQ No. should be unique';
                }
            }
            //with out po==//

            //Stock Return GRN==//
            if(isset($stock_grn_qty) && !empty($stock_grn_qty)){
                $ins_stock  =   array();
                $k=0;
                foreach($stock_grn_qty as $stock_grn_ky=>$stock_grn_val){
                    if($stock_grn_val!=""){
                        $ins_stock[$k]['order_id']                =   '0';
                        $ins_stock[$k]['po_number']               =   '';
                        $ins_stock[$k]['company_id']              =   $company_id;
                        $ins_stock[$k]['stock_id']                =   $stock_return_id[$stock_grn_ky];
                        $ins_stock[$k]['stock_return_for']        =   $stock_return_for[$stock_grn_ky];
                        $ins_stock[$k]['grn_no']                  =   $grn_no;
                        $ins_stock[$k]['grn_qty']                 =   $stock_grn_val;
                        $ins_stock[$k]['inventory_id']            =   $grn_inventory_id;
                        $ins_stock[$k]['last_updated_by']         =   $users['users_id'];
                        $ins_stock[$k]['approved_by']             =   $approved_by_stock[$stock_grn_ky];
                        $ins_stock[$k]['order_no']                =   '0';
                        $ins_stock[$k]['grn_type']                =   3;
                        $ins_stock[$k]['vendor_invoice_number']   =   $vendor_invoice_number_stock[$stock_grn_ky];
                        $ins_stock[$k]['vehicle_no_lr_no']        =   $vehicle_no_lr_no_stock[$stock_grn_ky];
                        $ins_stock[$k]['gross_wt']                =   $gross_wt_stock[$stock_grn_ky];
                        $ins_stock[$k]['gst_no']                  =   $gst_no_stock[$stock_grn_ky];
                        $ins_stock[$k]['frieght_other_charges']   =   $frieght_other_charges_stock[$stock_grn_ky];
                        $ins_stock[$k]['last_updated_date']       =   date('Y-m-d H:i:s');
                        $grn_no++;
                        $k++;
                    }
                }
                if(count($ins_stock)>0){
                    $this->db->insert_batch('grn_mgt', $ins_stock);
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                    $save_data = true;
                }
            }
            //Stock Return GRN==//

            $qryv = $this->db->select('id,indent_qty,grn_qty')->get_where('indent_mgt',array('inventory_id' => $grn_inventory_id, 'inv_status' => 1,'is_deleted' => '0'));
            //echo $this->db->last_query(); die;
            if($qryv->num_rows()){
                //echo "ss"; die;
                foreach($qryv->result() as $rows){
                    $reaming_qty = ($rows->indent_qty)-($rows->grn_qty);
                    if($grn_qty<=0){
                        break;
                    }
                    else if($grn_qty>=$reaming_qty){
                        $this->db->where('id',$rows->id);
                        $this->db->update('indent_mgt',array('grn_qty' => $rows->indent_qty,'closed_indent' => 1));
                        $grn_qty=($grn_qty)-($rows->indent_qty);

                    }
                    else{
                        $upd_qty = $rows->grn_qty+$grn_qty;
                        $this->db->where('id',$rows->id);
                        $this->db->update('indent_mgt',array('grn_qty' => $upd_qty));
                        $grn_qty=0;
                        break;
                    }
                }
                //===inventory===//
                $this->db->group_by('inventory_id');
                $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('inventory_id' => $grn_inventory_id, 'indent_qty >=' => '0', 'inv_status' => 1,'is_deleted' => '0'));
                $totenv=0;
                if($ind_qry->num_rows()){
                    $totenv=$ind_qry->row()->total_quantity;
                    //=======GRN Qty===//
                    $this->db->group_by('inventory_id');
                    $qry_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inventory_id' => $grn_inventory_id, 'inv_status' => 1,'grn_type'=>1, 'is_deleted' => 0));
                        // echo $this->db->last_query();die;
                    if($qry_grn_env->num_rows()){
                        $tot_grn = $qry_grn_env->row()->total_grn_quantity;
                    }

                    //=======GRN Qty===//
                    $totenv_verient = $totenv*(.02);
                    $tot_verify_env = $totenv - $totenv_verient;
                    if($tot_verify_env<=$tot_grn){
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('indent_mgt',array('inv_status' => '0', 'closed_indent' => '1'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('tbl_rfq',array('inv_status' => '0'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('grn_mgt',array('inv_status' => '0'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('issued_mgt',array('inv_status' => '0'));

                        $this->db->where('id',$grn_inventory_id);
                        $this->db->update('inventory_mgt',array('is_indent' => '0'));
                    }
                }
                //===inventory===//
                if($mp_msg==''){
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                }
                else{
                    $response['status']     =   '0';
                    $pre_msg = '';
                    if($save_data){
                        $pre_msg = 'GRN quantity updated but ';
                    }
                    $response['message']    =   $pre_msg.$mp_msg;
                }
                $save_data = true;
            }
            else{
                if($save_data){
                    if($mp_msg==''){
                        $response['status']     =   '1';
                        $response['message']    =   'GRN quantity updated successfully';
                    }
                    else{
                        $response['status']     =   '0';
                        $pre_msg = '';
                        if($save_data){
                            $pre_msg = 'GRN quantity updated but ';
                        }
                        $response['message']    =   $pre_msg.$mp_msg;
                    }

                }
                else{
                    $response['status']     =   '0';
                    if($mp_msg==''){
                        $response['message']    =   'GRN quantity not updated';
                    }
                    else{
                        $response['message']    =   $mp_msg;
                    }
                }
            }
            echo json_encode($response);
        }
    }

    public function update_grn_qty(){
        if($this->input->is_ajax_request()){

            $users              =   $this->session->userdata('auth_user');
            $grn_id             =   $this->input->post('grn_id');
            $grn_qty            =   $this->input->post('grn_qty');
            $approved_by            =   $this->input->post('approved_by');
            $i=0;
            if(isset($grn_id) && !empty($grn_id)){
                foreach($grn_id as $key => $vals){
                    $upd                        =   array();
                    $upd['grn_qty']             =   $grn_qty[$key];
                    $upd['approved_by']             =   $approved_by[$key];
                    $upd['last_updated_by']     =   $users['users_id'];
                    $upd['last_updated_date']   =   date('Y-m-d H:i:s');
                    $this->db->where('id',$vals);
                    $upqry = $this->db->update('grn_mgt',$upd);
                    if($upqry){
                        $i++;
                    }
                }
                if($i>0){
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'GRN quantity Not updated';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'No Data Found';
            }
            echo json_encode($response);
        }
    }

    public function delete_grn_qty(){
        if($this->input->is_ajax_request()){
            $users              =   $this->session->userdata('auth_user');
            $grn_id             =   $this->input->post('grn_id');
            if(isset($grn_id) && !empty($grn_id)){
                $upd                        =   array();
                $upd['last_updated_by']     =   $users['users_id'];
                $upd['last_updated_date']   =   date('Y-m-d H:i:s');
                $upd['is_deleted']          =   1;
                $this->db->where_in('id',$grn_id);
                $upqry = $this->db->update('grn_mgt',$upd);
                if($upqry){
                    $response['status']     =   '1';
                    $response['message']    =   'GRN Deleted successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'GRN Not Deleted';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'No Data Found';
            }
            echo json_encode($response);
        }
    }
public function get_issued_details(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');
            //==inventory data==//
            $this->db->select("inv.*,tp.prod_name,tp.cat_id,tb.factory_name,tu.uom_name,tusr.first_name,tusr.last_name,inv.opening_stock", false);
            $this->db->from("inventory_mgt as inv");
            $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
            $this->db->join("buyer_factory_details as tb",'tb.id=inv.branch_id', 'LEFT');
            $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
            $this->db->join("tbl_users as tusr",'tusr.id=inv.added_by', 'LEFT');
            $this->db->where('inv.id',$inventory_id);
            $query = $this->db->get();
            //==inventory data==//
            if($query->num_rows()){
                $inventory_details['inv']   =   $query->row_array();
                $data                       =   $query->row();
                $final_arr                  =   array();
                $final_arr_qty              =   array();
                if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){
                    $final_arr['0']     =   'Opening Stock';
                    $final_arr_qty['0'] =   $data->opening_stock;
                }
                //===GRN Details===//
                // $this->db->select('SUM(grn_qty) as total_grn_quantity, MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                // $this->db->where('inventory_id',$inventory_id);
                // $this->db->group_by('inventory_id');
                // $this->db->group_by('po_number');
                // $this->db->where('is_deleted',0);
                // $this->db->where('grn_type',1);
                // $qry_grn = $this->db->get('grn_mgt');

                $this->db->select('grn_qty as total_grn_quantity,id,vendor_name,grn_type,order_no,inventory_id,po_number');
                $this->db->where('inventory_id',$inventory_id);
                //$this->db->group_by('inventory_id');
                //this->db->group_by('po_number');
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type',1);
                $qry_grn = $this->db->get('grn_mgt');

                if($qry_grn->num_rows()){
                    $grn_datas = $qry_grn->result();
                    $po_number = array();
                    foreach($qry_grn->result() as $key=> $val){
                        if($val->grn_type == 1){
                            $po_number[] = $val->po_number;
                        }
                    }

                    //==Store Details==//
                    $get_store_name =   array();
                    if(isset($po_number) && !empty($po_number)){
                        $this->db->select('tro.po_number,trp.vend_user_id,ts.store_name');
                        $this->db->join('tbl_rfq_price as trp','trp.id =tro.price_id');
                        $this->db->join('tbl_users as tu','tu.id =trp.vend_user_id');
                        $this->db->join('tbl_store as ts','ts.store_id =tu.store_id');
                        $this->db->where_in('tro.po_number',$po_number);
                        $qry = $this->db->get('tbl_rfq_order tro');
                        if($qry->num_rows()){
                            foreach($qry->result() as $keys => $vals){
                                $get_store_name[$vals->po_number] =$vals->store_name;
                            }
                        }
                    }
                    //==Store Details==//
                }
                 /////////manual order
                 //$this->db->select('SUM(grn_qty) as total_grn_quantity, MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                 $this->db->select('grn_qty as total_grn_quantity,id,vendor_name,grn_type,order_no, order_id, inventory_id,po_number');
                 $this->db->where('inventory_id',$inventory_id);
                 //  $this->db->group_by('inventory_id');
                 //$this->db->group_by('po_number');
                 $this->db->where('is_deleted',0);
                 $this->db->where('grn_type',4);
                 $qry_manual_grn = $this->db->get('grn_mgt');
                 if($qry_manual_grn->num_rows()){
                     if(isset($grn_datas)){
                        //pr($qry_manual_grn->result()); die;
                         // $grn_datas = $grn_datas + $qry_grn2->result();
                         $grn_datas =array_merge($grn_datas , $qry_manual_grn->result()) ;
                     }
                     else{
                         $grn_datas = $qry_manual_grn->result();
                     }
                     $manual_po_number = array();
                     foreach($qry_manual_grn->result() as $key=> $val){
                         if($val->grn_type == 4){
                             $manual_po_number[] = $val->po_number;
                         }
                     }
                    //pr($grn_datas); die;
                     //==Store Details==//
                     // $get_store_name =   array();
                     if(isset($manual_po_number) && !empty($manual_po_number)){
                         $this->db->select('(`mpo`.`manual_po_number`), `mpo`.`vendor_id` as `vend_user_id`, `ts`.`store_name`');
                         $this->db->join('tbl_store as ts','ts.store_id =mpo.vendor_id');
                         $this->db->where_in('mpo.manual_po_number',$manual_po_number);
                         $manual_qry = $this->db->get('tbl_manual_po_order mpo');
                         if($manual_qry->num_rows()){
                             foreach($manual_qry->result() as $keys => $vals){
                                 $get_store_name[$vals->manual_po_number] =$vals->store_name;
                             }
                         }
                     //
                     }
                     //==Store Details==//
                 }
                 ///////////manual order
                $this->db->select('grn_qty as total_grn_quantity, id,vendor_name,grn_type,order_no,order_id,inventory_id,po_number');
                $this->db->where('inventory_id',$inventory_id);
                //$this->db->group_by('inventory_id');
                //$this->db->group_by('po_number');
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type',2);
                $qry_grn2 = $this->db->get('grn_mgt');
                // echo $this->db->last_query();die;
                if($qry_grn2->num_rows()){
                    if(isset($grn_datas)){
                        // $grn_datas = $grn_datas + $qry_grn2->result();
                        $grn_datas = array_merge($grn_datas , $qry_grn2->result());
                    }
                    else{
                        $grn_datas = $qry_grn2->result();
                    }
                }
                //===get grn again stock return===//
                $stock_grn_add_qty = array();
                // $this->db->group_by('inventory_id');
                // $this->db->group_by('stock_return_for');
                // $stock_grn_qry = $this->db->select('SUM(grn_qty) as total_grn_quantity,MAX(stock_return_for) as stock_return_for')->get_where('grn_mgt',array('inventory_id' => $inventory_id, 'grn_type' => '3', 'is_deleted' => '0'));

                $stock_grn_qry = $this->db->select('grn_qty as total_grn_quantity,stock_return_for')->get_where('grn_mgt',array('inventory_id' => $inventory_id, 'grn_type' => '3', 'is_deleted' => '0'));
                if($stock_grn_qry->num_rows()){
                    foreach($stock_grn_qry->result() as $stgrn_row){
                        if($stgrn_row->stock_return_for==0){
                            $final_arr_qty['0'] =   $final_arr_qty['0']+$stgrn_row->total_grn_quantity;
                        }
                        else{
                            if(isset( $stock_grn_add_qty[$stgrn_row->stock_return_for])){
                                $stock_grn_add_qty[$stgrn_row->stock_return_for]=$stock_grn_add_qty[$stgrn_row->stock_return_for]+$stgrn_row->total_grn_quantity;
                            }
                            else{
                                $stock_grn_add_qty[$stgrn_row->stock_return_for]=$stgrn_row->total_grn_quantity;
                            }
                        }
                    }
                }
                //===get grn again stock return===//
                //===GRN Details===//
                //===issued data===//
                $total_issued               =   0;
                $total_issued_for    = array();
                $this->db->where('inventory_id',$inventory_id);
                //$this->db->group_by('inventory_id');
                //$this->db->group_by('issued_return_for');
                //$qry_issued = $this->db->select('SUM(qty) AS total_issued,MAX(issued_return_for) as issued_return_for,MAX(inventory_id) as inventory_id')->get_where('issued_mgt', array('is_deleted' => '0'));
                $qry_issued = $this->db->select('qty as total_issued,issued_return_for,inventory_id')->get_where('issued_mgt', array('is_deleted' => '0'));
                if($qry_issued->num_rows()){
                    foreach($qry_issued->result() as $isrw){
                        $total_issued  =   $isrw->total_issued;
                        if($isrw->issued_return_for==0){
                            $final_arr_qty['0'] = $final_arr_qty['0']-$isrw->total_issued;
                        }
                        //$total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                        if(isset($total_issued_for[$isrw->issued_return_for])){
                            $total_issued_for[$isrw->issued_return_for]=$total_issued_for[$isrw->issued_return_for]+$isrw->total_issued;
                        }else{
                            $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                        }
                    }
                }
                //pr($total_issued_for); die;
                //pr($stock_grn_add_qty); die;
                //===issued data===//
                if(isset($grn_datas) && !empty($grn_datas)){
                    foreach($grn_datas as $key => $vals){
                        if($vals->grn_type == 2){
                            $final_arr[$vals->id] =  $vals->order_no.'/'.$vals->vendor_name;
                        }else if($vals->grn_type == 1 || $vals->grn_type == 4){
                            $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                        }
                        if(isset($stock_grn_add_qty[$vals->id]) && !empty($stock_grn_add_qty[$vals->id])){
                            $final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->id];
                            //$final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->inventory_id];
                        }
                        else{
                            $final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->id];
                            //$final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->inventory_id];
                        }
                    }
                }
                ///pr($final_arr_qty); die;
                //===Issued Return Data===//
                $this->db->where('inventory_id',$inventory_id);
                $this->db->group_by('inventory_id');
                $this->db->group_by('issued_return_for');
                $qry_issued_return = $this->db->select('SUM(qty) AS total_issued_return,MAX(issued_return_for) as issued_return_for')->get_where('issued_return_mgt', array('is_deleted' => '0'));
                if($qry_issued_return->num_rows()){
                    foreach($qry_issued_return->result() as $isretw){
                        if($isretw->issued_return_for==0){
                            $final_arr_qty['0'] = $final_arr_qty['0']+$isretw->total_issued_return;
                        }
                        else{
                            $final_arr_qty[$isretw->issued_return_for]=$final_arr_qty[$isretw->issued_return_for] + $isretw->total_issued_return;
                        }
                    }
                }
                //===Issued Return Data===//
                //====Stock Return====//
                $this->db->where('inventory_id',$inventory_id);
                $this->db->group_by('inventory_id');
                $this->db->group_by('stock_return_for');
                $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(stock_return_for) as stock_return_for')->get_where('tbl_return_stock', array('is_deleted' => '0'));
                if($qry_stock_return->num_rows()){
                    foreach($qry_stock_return->result() as $stock_ret_row){
                        $final_arr_qty[$stock_ret_row->stock_return_for] = $final_arr_qty[$stock_ret_row->stock_return_for]-$stock_ret_row->total_stock_return;
                    }
                }
                $chk_array=array();
                foreach($final_arr as $flkey => $flvals){
                   if(isset($chk_array[$flvals])){
                        $final_arr_qty[$flkey] = $final_arr_qty[$flkey]+$final_arr_qty[$chk_array[$flvals]];
                        unset($final_arr[$chk_array[$flvals]]);
                        unset($final_arr_qty[$chk_array[$flvals]]);
                   }
                   else{
                        $chk_array[$flvals]=$flkey;
                   }
                }
                //====Stock Return====//
                $chk_array=array();
                foreach($final_arr as $flkey => $flvals){
                    if(isset($chk_array[$flvals])){
                            $final_arr_qty[$flkey] = $final_arr_qty[$flkey]+$final_arr_qty[$chk_array[$flvals]];
                            unset($final_arr[$chk_array[$flvals]]);
                            unset($final_arr_qty[$chk_array[$flvals]]);
                    }
                    else{
                            $chk_array[$flvals]=$flkey;
                    }
                }
                foreach($final_arr_qty as $fkey => $fvals){
                    if($fvals<=0){
                        unset($final_arr_qty[$fkey]);
                        unset($final_arr[$fkey]);
                    }
                }

                $issued_type = array();
                $qry_issued_type    =   $this->db->select('id,name')->get_where('issued_type',array('status' => '1'));
                if($qry_issued_type->num_rows()){
                    foreach($qry_issued_type->result() as $isu_tp){
                        $issued_type[$isu_tp->id] = $isu_tp->name;
                    }
                }
                $users          =   $this->session->userdata('auth_user');
                if($users['parent_id'] != '') {
                    $user_ids  =   $users['parent_id'];
                } else {
                    $user_ids   =  $users['users_id'];
                }
                $issue_to = array();
                $qry_issue_to = $this->db->select('id,name')->get_where('issue_to_mgt',array('user_id' => $user_ids));
                if($qry_issue_to->num_rows()){
                    foreach($qry_issue_to->result() as $isu_tn){
                        $issue_to[$isu_tn->id] = $isu_tn->name;
                    }
                }
                // echo $inventory_details['inv']['opening_stock'].'--'.$total_grn.'---'.$total_issued
                $inventory_details['my_default_stock']  =   isset($final_arr_qty[0]) ? $final_arr_qty[0] : '0';
                $inventory_details['return_for']        =   $final_arr;
                $inventory_details['return_for_qty']    =   $final_arr_qty;
                $inventory_details['total_grn']         =   $total_grn;
                $inventory_details['current_stock']     =   ($inventory_details['inv']['opening_stock']+$total_grn)-($total_issued);
                $inventory_details['total_issued']      =   $total_issued;
                $inventory_details['issued_type']       =   $issued_type;
                $inventory_details['issue_to']          =   $issue_to;
                $response['status']     =   '1';
                $response['data']       =   $inventory_details;
                $response['message']    =   'Enventory Details Succesfully Fetched Against selected Inventory';
            }
            else{
                $response['data']         =   [];
                $response['status']       =   '0';
                $response['message']      =   'No inventory details Found';
            }
        }
        echo json_encode($response); die;
    }
    public function get_issued_details_olsddd(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');
            //==inventory data==//
            $this->db->select("inv.*,tp.prod_name,tp.cat_id,tb.factory_name,tu.uom_name,tusr.first_name,tusr.last_name,inv.opening_stock", false);
            $this->db->from("inventory_mgt as inv");
            $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
            $this->db->join("buyer_factory_details as tb",'tb.id=inv.branch_id', 'LEFT');
            $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
            $this->db->join("tbl_users as tusr",'tusr.id=inv.added_by', 'LEFT');
            $this->db->where('inv.id',$inventory_id);
            $query = $this->db->get();
            //==inventory data==//
            if($query->num_rows()){
                $inventory_details['inv']   =   $query->row_array();
                $data                       =   $query->row();
                $final_arr                  =   array();
                $final_arr_qty              =   array();
                if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){
                    $final_arr['0']     =   'Opening Stock';
                    $final_arr_qty['0'] =   $data->opening_stock;
                }
                //===GRN Details===//
                $this->db->select('id,vendor_name,grn_type,order_no,order_id,inventory_id,po_number,grn_qty');
                $this->db->where('inventory_id',$inventory_id);
                // $this->db->group_by('inventory_id');
                // $this->db->group_by('po_number');
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type !=',3);
                $qry_grn = $this->db->get('grn_mgt');
                if($qry_grn->num_rows()){
                    $grn_datas = $qry_grn->result();
                    $po_number = array();
                    $total_grn_quantity = 0;
                    foreach($qry_grn->result() as $key=> $val){
                        $total_grn_quantity += $val->grn_qty;

                        if($val->grn_type == 1){
                            $po_number[] = $val->po_number;
                        }
                    }
                    $grn_datas->total_grn_quantity = $total_grn_quantity;
                    //===get grn again stock return===//
                    $stock_grn_add_qty = array();
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('stock_return_for');
                    $stock_grn_qry = $this->db->select('SUM(grn_qty) as total_grn_quantity,MAX(stock_return_for) as stock_return_for')->get_where('grn_mgt',array('inventory_id' => $inventory_id, 'grn_type' => '3', 'is_deleted' => '0'));
                    if($stock_grn_qry->num_rows()){
                        foreach($stock_grn_qry->result() as $stgrn_row){
                            if($stgrn_row->stock_return_for==0){
                                $final_arr_qty['0'] =   $final_arr_qty['0']+$stgrn_row->total_grn_quantity;
                            }
                            else{
                                $stock_grn_add_qty[$stgrn_row->stock_return_for]=$stgrn_row->total_grn_quantity;
                            }
                        }
                    }
                    //===get grn again stock return===//
                    //==Store Details==//
                    $get_store_name =   array();
                    if(isset($po_number) && !empty($po_number)){
                        $this->db->select('tro.po_number,trp.vend_user_id,ts.store_name');
                        $this->db->join('tbl_rfq_price as trp','trp.id =tro.price_id');
                        $this->db->join('tbl_users as tu','tu.id =trp.vend_user_id');
                        $this->db->join('tbl_store as ts','ts.store_id =tu.store_id');
                        $this->db->where_in('tro.po_number',$po_number);
                        $qry = $this->db->get('tbl_rfq_order tro');
                        if($qry->num_rows()){
                            foreach($qry->result() as $keys => $vals){
                                $get_store_name[$vals->po_number] =$vals->store_name;
                            }
                        }
                    }
                    //==Store Details==//
                }
                //===GRN Details===//
                //===issued data===//
                $total_issued               =   0;
                $total_issued_for    = array();
                $this->db->where('inventory_id',$inventory_id);
                $this->db->group_by('inventory_id');
                $this->db->group_by('issued_return_for');
                $qry_issued = $this->db->select('SUM(qty) AS total_issued,MAX(issued_return_for) as issued_return_for')->get_where('issued_mgt', array('is_deleted' => '0'));
                if($qry_issued->num_rows()){
                    foreach($qry_issued->result() as $isrw){
                        $total_issued  =   $isrw->total_issued;
                        if($isrw->issued_return_for==0){
                            $final_arr_qty['0'] = $final_arr_qty['0']-$isrw->total_issued;
                        }
                        $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                    }
                }
                //===issued data===//
                pr($grn_datas);
                if(isset($grn_datas) && !empty($grn_datas)){
                    foreach($grn_datas as $key => $vals){
                        if($vals->grn_type == 2){
                            $final_arr[$vals->id] =  $vals->order_no.'/'.$vals->vendor_name;
                        }else if($vals->grn_type == 1){
                            $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                        }
                        if(isset($stock_grn_add_qty[$vals->id]) && !empty($stock_grn_add_qty[$vals->id])){
                            $final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->id];
                        }
                        else{
                            $final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->id];
                        }
                    }
                    pr( $final_arr);
                }
                //===Issued Return Data===//
                $this->db->where('inventory_id',$inventory_id);
                $this->db->group_by('inventory_id');
                $this->db->group_by('issued_return_for');
                $qry_issued_return = $this->db->select('SUM(qty) AS total_issued_return,MAX(issued_return_for) as issued_return_for')->get_where('issued_return_mgt', array('is_deleted' => '0'));
                if($qry_issued_return->num_rows()){
                    foreach($qry_issued_return->result() as $isretw){
                        if($isretw->issued_return_for==0){
                            $final_arr_qty['0'] = $final_arr_qty['0']+$isretw->total_issued_return;
                        }
                        else{
                            $final_arr_qty[$isretw->issued_return_for]=$final_arr_qty[$isretw->issued_return_for] + $isretw->total_issued_return;
                        }
                    }
                }
                //===Issued Return Data===//
                //====Stock Return====//
                $this->db->where('inventory_id',$inventory_id);
                $this->db->group_by('inventory_id');
                $this->db->group_by('stock_return_for');
                $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(stock_return_for) as stock_return_for')->get_where('tbl_return_stock', array('is_deleted' => '0'));
                if($qry_stock_return->num_rows()){
                    foreach($qry_stock_return->result() as $stock_ret_row){
                        $final_arr_qty[$stock_ret_row->stock_return_for] = $final_arr_qty[$stock_ret_row->stock_return_for]-$stock_ret_row->total_stock_return;
                    }
                }
                    pr( $final_arr_qty);

                //====Stock Return====//
                foreach($final_arr_qty as $fkey => $fvals){
                    if($fvals<=0){
                        unset($final_arr_qty[$fkey]);
                        unset($final_arr[$fkey]);
                    }
                }
                    //pr( $final_arr);die;


                $issued_type = array();
                $qry_issued_type    =   $this->db->select('id,name')->get_where('issued_type',array('status' => '1'));
                if($qry_issued_type->num_rows()){
                    foreach($qry_issued_type->result() as $isu_tp){
                        $issued_type[$isu_tp->id] = $isu_tp->name;
                    }
                }
                $inventory_details['my_default_stock']  =   isset($final_arr_qty[0]) ? $final_arr_qty[0] : '0';
                $inventory_details['return_for']        =   $final_arr;
                $inventory_details['return_for_qty']    =   $final_arr_qty;
                $inventory_details['total_grn']         =   $total_grn;
                $inventory_details['current_stock']     =   ($inventory_details['inv']['opening_stock']+$total_grn)-($total_issued);
                $inventory_details['total_issued']      =   $total_issued;
                $inventory_details['issued_type']       =   $issued_type;
                $response['status']     =   '1';
                $response['data']       =   $inventory_details;
                $response['message']    =   'Enventory Details Succesfully Fetched Against selected Inventory';
            }
            else{
                $response['data']         =   [];
                $response['status']       =   '0';
                $response['message']      =   'No inventory details Found';
            }
        }
        echo json_encode($response); die;
    }

    public function get_issued_details_1may24(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');
            $this->db->where('grn.inventory_id',$inventory_id);
            $this->db->select("grn.id,grn.grn_qty,grn.inventory_id,tocd.vendor_id,tocd.order_confirmation_id,tocd.rfq_number,tocd.po_number,tocd.quantity,tocd.created_at,tu.first_name", false);
            $this->db->from("grn_mgt as grn");
            $this->db->join("tbl_order_confirmation_details as tocd","tocd.order_confirmation_id=grn.order_id");
            $this->db->join("tbl_users as tu",'tu.id=tocd.vendor_id');
            $this->db->where('tocd.order_status','1');
            //$this->db->where('grn.inv_status','1');
            $qry_grn = $this->db->get();
            // echo "sss"; die;
            // echo $this->db->last_query(); die;
            if($qry_grn->num_rows()){
                $grn_id_arr = array();
                foreach($qry_grn->result() as $grw){
                    $grn_id_arr[$grw->id]=$grw->id;
                }
                //=======issued Qty===//
                $issued_grn_details    =   array();
                $this->db->where_in('grn_id',$grn_id_arr);
                $this->db->group_by('grn_id');
                $this->db->group_by('inventory_id');
                //$qry_issued_env = $this->db->select('SUM(qty) AS total_issued,grn_id,inventory_id')->get_where('issued_mgt',array('inv_status' => '1'));
                $qry_issued_env = $this->db->select('SUM(qty) AS total_issued,grn_id,MAX(inventory_id) as inventory_id')->get_where('issued_mgt');
                //echo $this->db->last_query(); die;
                if($qry_issued_env->num_rows()){
                    foreach($qry_issued_env->result() as $issued_res){
                        $issued_grn_details[$issued_res->grn_id][$issued_res->inventory_id]    =   $issued_res->total_issued;
                    }
                }
                //pr($issued_grn_details); die;
                $grn_deatils=array();
                foreach($qry_grn->result() as $key => $grn_res){
                    $grn_deatils[$key]=$grn_res;
                    $grn_deatils[$key]->issued_quantity=isset($issued_grn_details[$grn_res->id][$grn_res->inventory_id]) ? $issued_grn_details[$grn_res->id][$grn_res->inventory_id] : 0;
                }
                //=======issued Qty===//
                //pr($ord_deatils); die;
                $response['status']     =   '1';
                $response['data']       =   $grn_deatils;
                $response['message']    =   'GRN Details Succesfully Fetched Against selected Inventory';
            }else{
              $response['data']         =   [];
              $response['status']       =   '0';
              $response['message']      =   'No GRN details Found';
            }
            echo json_encode($response);
        }
    }

    public function save_issued_qty(){
        if($this->input->is_ajax_request()){
            $response               =   array();
            $users                  =   $this->session->userdata('auth_user');
            $remarks                =   $this->input->post('remarks');
            $issued_to              =   $this->input->post('issued_to');
            $issued_qty             =   $this->input->post('issued_qty');
            $issued_inventory_id    =   $this->input->post('issued_inventory_id');
            $issued_return_for      =   $this->input->post('issued_return_for');
            $issued_type            =   $this->input->post('issued_type');
            if(isset($issued_qty) && $issued_qty!="" && isset($issued_inventory_id) && $issued_inventory_id!=""){
                if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
                $max_issued_no =   1;
                $vrify_qry = $this->db->select_max("issued_no")->get_where('issued_mgt',array('company_id' => $company_id));
                if($vrify_qry->num_rows()){
                    $row_data               =   $vrify_qry->row();
                    $max_issued_no          =   ($row_data->issued_no)+1;
                }
                $ins['company_id']          =   $company_id;
                $ins['inventory_id']        =   $issued_inventory_id;
                $ins['issued_no']           =   $max_issued_no;
                $ins['remarks']             =   $remarks;
                $ins['issued_to']           =   $issued_to;
                $ins['issued_return_for']   =   $issued_return_for;
                $ins['qty']                 =   $issued_qty;
                $ins['issued_type']         =   $issued_type;
                $ins['last_updated_by']     =   $users['users_id'];
                $ins['last_updated_date']   =   date('Y-m-d H:i:s');

                $qry =  $this->db->insert('issued_mgt', $ins);
                if($qry){
                    $response['status']     =   '1';
                    $response['message']    =   'Issued quantity updated successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Issued quantity not updated';
                }
            }
            echo json_encode($response); die;
        }
    }

    public function closed_inventory(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Closed Indent";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch   =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $this->load->view('inventory_management/closed_inventory_list',$data);
    }

    public function get_closed_inventory_data()
    {
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $users_ids = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_closed_inventory_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record = $this->inventory_management_model->get_closed_inventory_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $invarrs    = array();
        $totindqty  = array();
        $totgrnqty  = array();

        if (isset($result) && !empty($result)) {
            foreach ($result as $resp_val) {
                $invarrs[$resp_val->id] = $resp_val->id;
            }
            $response_totindqty     =   $this->get_totindqty($invarrs);
            $totindqty              =   $response_totindqty;
            //pr($totindqty); die;
            //====grn===//
            $response_totgrnqty     =   $this->get_totgrnqty($invarrs);
            $totgrnqty              =   $response_totgrnqty;
            //====grn===//

        }
        $data1 = array();
        foreach ($result as $key => $val) {
            $sub_array =array();
            $total_quantity     =   $totindqty[$val->id] ?? 0;
            $grn_qty            =   $totgrnqty[$val->id] ?? 0;
            $sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_' . $val->id . '"  class="pr-3 accordion_parent accordion_parent_' . $val->id . '" tab-index="' . $val->id . '" onclick="close_indent_tds(' . $val->id . ')"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer" id="plus_' . $val->id . '" class="pr-3 accordion_parent accordion_parent_' . $val->id . '" tab-index="' . $val->id . '" onclick="open_indent_tds(' . $val->id . ')"><i class="bi bi-plus-lg"></i></span>';
            $sub_array[] = $val->prod_name;
            $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] = strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[] = strlen($val->inventory_grouping)<=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
            $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $sub_array[] = $val->uom_name;
            $sub_array[] = '<span id="total_indent_qty_' . $val->id . '">' . round($total_quantity,2). '</span>';
            $sub_array[] = '<span id="total_order_rfq_qty_' . $val->id . '" >' . round($total_quantity,2) . '</span>';
            $sub_array[] = '<span id="total_inven_orders_quan' . $val->id . '" >' . round($total_quantity,2) . '</span>';
            $sub_array[] = '<span id="inven_grn_quan_' . $val->id . '" >' . round($grn_qty,2) . '</span>';
            $data1[] = $sub_array;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function get_totindqty($invarrs) {
        $query = $this->db->select('MAX(inventory_id) as inventory_id, SUM(indent_qty) AS total_quantity', false)
            ->from('indent_mgt')
            ->where('indent_qty >=', 0)
            ->where('inv_status !=', 1)
            ->where_in('inventory_id', array_filter($invarrs)) // Ensure only valid values
            ->group_by('inventory_id')
            ->get();

        $totindqty = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $inds_resp) {
                $totindqty[$inds_resp->inventory_id] = $inds_resp->total_quantity;
            }
        }

        return $totindqty;
    }

    public function get_totgrnqty($invarrs) {
        $query = $this->db->select('MAX(inventory_id) as inventory_id, SUM(grn_qty) AS total_grn', false)
            ->from('grn_mgt')
            ->where('grn_qty >=', 0)
            ->where('inv_status !=', 1)
            ->where('grn_type', 1)
            ->where_in('inventory_id', array_filter($invarrs)) // Ensure only valid values
            ->group_by('inventory_id')
            ->get();

        $totgrnqty = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $grn_resp) {
                $totgrnqty[$grn_resp->inventory_id] = $grn_resp->total_grn;
            }
        }

        return $totgrnqty;
    }

    public function export_closed_inventory()
    {
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        // Determine parent user ID
        if (!empty($users['parent_id'])) {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        // Fetch the buyer user IDs based on parent ID
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        // Get the closed inventory data
        $result = $this->inventory_management_model->get_closed_inventory_data($users_ids, $buyer_users, 'page', $cat_id);
        // print_r($result);
        // Get the total records
        $total_record = $this->inventory_management_model->get_closed_inventory_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $invarrs    = array();
        $totindqty  = array();
        $totgrnqty  = array();

        if (isset($result) && !empty($result)) {
            foreach ($result as $resp_val) {
                $invarrs[$resp_val->id] = $resp_val->id;
            }
            $this->db->where_in('inventory_id', $invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt', array('indent_qty >=' => '0', 'inv_status !=' => 1));

            if ($ind_qry->num_rows()) {
                foreach ($ind_qry->result() as $inds_resp) {
                    $totindqty[$inds_resp->inventory_id] = $inds_resp->total_quantity;
                }
            }
            //====grn===//
            $this->db->where_in('inventory_id', $invarrs);
            $this->db->group_by('inventory_id');
            $grn_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(grn_qty) AS total_grn')->get_where('grn_mgt', array('grn_qty >=' => '0', 'inv_status !=' => 1));

            if ($grn_qry->num_rows()) {
                foreach ($grn_qry->result() as $grn_resp) {
                    $totgrnqty[$grn_resp->inventory_id] = $grn_resp->total_grn;
                }
            }
            //====grn===//

        }
        $final_data = [];
        $i = 0;
        foreach ($result as $key => $val) {
            $total_quantity =   isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            $grn_qty        =   isset($totgrnqty[$val->id]) ? $totgrnqty[$val->id] : 0;

            $sub_array = array();
            //listing------export_grn_report
            $final_data[$i]['Product']          =   $val->prod_name;
            $final_data[$i]['Specification']    =   HtmlDecodeString($val->specification);
            $final_data[$i]['Size']             =   HtmlDecodeString($val->size);
            $final_data[$i]['grouping']         =   HtmlDecodeString($val->inventory_grouping);
            $final_data[$i]['User']             =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['UOM']              =   $val->uom_name;
            $final_data[$i]['Indent Qty']       =   round($total_quantity,2);
            $final_data[$i]['Rfq Qty']          =   round($total_quantity,2);
            $final_data[$i]['Order Qty']        =   round($total_quantity,2);
            $final_data[$i]['GRN Qty']          =   round($grn_qty,2);

            $i++;
        }


        $data['count'] = count($final_data);
        $data['data'] = $final_data;

        echo json_encode($data);
    }

    public function fetch_closed_indent_data(){
        if($this->input->is_ajax_request()){
            $inventory            =   $this->input->post('inventory');
            if($inventory){
                //$qry = $this->db->get_where('indent_mgt', array('inventory_id' => $inventory));
                $this->db->select("ind.id,ind.last_updated_date,ind.inventory_id,ind.comp_br_sp_ind_id,ind.indent_qty,ind.grn_qty,ind.remarks,ind.last_updated_by,tusr.first_name,tusr.last_name,tusr2.first_name as Addfname,tusr2.last_name as Addedlname", false);
                $this->db->from("indent_mgt as ind");
                $this->db->join("tbl_users as tusr",'tusr.id=ind.last_updated_by', 'LEFT');
                $this->db->join("tbl_users as tusr2",'tusr2.id=ind.created_by', 'LEFT');
                $this->db->where('ind.inventory_id',$inventory);
                $this->db->where('ind.inv_status',0);
                $query = $this->db->get();
                if($query->num_rows()){
                    $res['status']          =   1;
                    $res['message']         =   'Indent  found';
                    $res['resp']            =   $query->result();
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Indent not found';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Indent not found';
            echo json_encode($res); die;
        }
    }

    public function issued_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
            $this->db->select("first_name, last_name,id");
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query1 = $this->db->get();
            if ($query1->num_rows() > 0) {
                $data['user_name']= $query1->row()->first_name." ".$query1->row()->last_name;
                $data['user_id']= $query1->row()->id;
            }
        } else {
            $user_id   =  $users['users_id'];

            $data['user_id']=$user_id;
            $this->db->select("first_name, last_name");
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query1 = $this->db->get();
            if ($query1->num_rows() > 0) {
                $data['user_name']= $query1->row()->first_name." ".$query1->row()->last_name;
            }
        }
        $data['issue_to_mgt']       =   $this->inventory_management_model->get_issue_to_data($user_id);
        $data['page_title']         =   "Issued Report Management";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch   =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        $data['currency_list']      =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']     =   $buyer_currency;
        $this->load->view('inventory_management/issued_report_list',$data);
    }

    public function get_issued_report_data()
    {
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_issued_report_data_new($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_issued_report_data_new($users_ids, $buyer_users, 'total',$cat_id);
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];

        $data1 = array();
        $issued_return_for_arr = array();
        $invarrs        =   array();
        $grn_price_arr  =   array();
        foreach ($result as $key => $vals) {
            $invarrs[$vals->inventory_id]               =   $vals->inventory_id;
            $grn_price_arr['os'][$vals->inventory_id]   =   $vals->stock_price;
            //$issued_return_for_arr[$vals->issued_return_for] = $vals->issued_return_for;
        }
        if(isset($invarrs) && !empty($invarrs)){
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number][$rp_row->inventory_id] = $rp_row->order_price;
                }
            }
            //====wpo price===//
            //====manual po price===//
            $manualpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('tbl_manual_po_order',array('product_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $manualpo_price[$rp_row->manual_po_number][$rp_row->inventory_id] = $rp_row->product_price;
                }
            }
            //====manual po price===//

            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    }
                    else{
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    }
                    $grn_price_arr[$grn_wp_res->id] =   isset($grn_wp_res->grn_buyer_rate) && $grn_wp_res->grn_buyer_rate>0 ? $grn_wp_res->grn_buyer_rate : $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                }
            }
            //pr($grn_wpo_arr); die;
            //===GRN WPO====//
            //===GRN manualPO====//
            $grn_manualpo_arr        =   array();
            $grn_manualpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_manualp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')->get_where('grn_mgt',array('grn_type' => '4', 'is_deleted' => '0'));
            if($qry_grn_manualp->num_rows()){
                foreach($qry_grn_manualp->result() as $grn_manualp_res){
                    if(isset($grn_manualpo_arr[$grn_manualp_res->inventory_id])){
                        $grn_manualpo_arr[$grn_manualp_res->inventory_id]    =  $grn_manualpo_arr[$grn_manualp_res->inventory_id] + $grn_manualp_res->grn_qty;
                    }
                    else{
                        $grn_manualpo_arr[$grn_manualp_res->inventory_id]    =   $grn_manualp_res->grn_qty;
                    }
                    if(isset($grn_manualpo_price_arr[$grn_manualp_res->inventory_id])){
                        $grn_manualpo_price_arr[$grn_manualp_res->inventory_id]    =  $grn_manualpo_price_arr[$grn_manualp_res->inventory_id] + $grn_manualp_res->grn_qty*$manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                    }
                    else{
                        $grn_manualpo_price_arr[$grn_manualp_res->inventory_id]    =   $grn_manualp_res->grn_qty*$manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                    }
                    $grn_price_arr[$grn_manualp_res->id] =   isset($grn_manualp_res->grn_buyer_rate) && $grn_manualp_res->grn_buyer_rate>0 ? $grn_manualp_res->grn_buyer_rate : $manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                }
            }
            //pr($grn_wpo_arr); die;
            //===GRN manualPO====//


            //===GRN WOPO====//
            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*($grn_wop_res->rate));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*($grn_wop_res->rate);
                    }
                    $grn_price_arr[$grn_wop_res->id] =   $grn_wop_res->rate;
                }
            }

            //===GRN WOPO====//
        }
        //====Get all grn  value====//
        $sr_no = 1;
        // pr($result);die;
        //====Issue to data====//
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $issue_to_list = array();
        $issue_to_qry = $this->db->get_where('issue_to_mgt',array('user_id' => $user_id));
        if($issue_to_qry->num_rows()){
            foreach($issue_to_qry->result() as $itdrow){
                $issue_to_list[$itdrow->id]    =   $itdrow->name;
            }
        }
        //===issue to data====//
        foreach ($result as $key => $val) {

            $sub_array = array();
            //listing------
            //$sub_array[] = $sr_no;
            $max_allow_qty=0;
            $qry = $this->db->select('inventory_id,issued_return_for')->get_where('issued_mgt',array('id' => $val->id));
                if($qry->num_rows()){
                    $issued_data        =   $qry->row();
                    $inventory_id       =   $issued_data->inventory_id;
                    $issued_return_for  =   $issued_data->issued_return_for;
                    //===get total issued return==//
                    $issue_retn_qry     =   $this->db->select('qty')->get_where('issued_return_mgt',array('inventory_id' => $inventory_id, 'issued_return_for' => $issued_return_for));
                    $tot_issued_return  =   0;
                    if($issue_retn_qry->num_rows()){
                        foreach($issue_retn_qry->result() as $ir_rows){
                            $tot_issued_return = $tot_issued_return+$ir_rows->qty;
                        }
                    }

                    //===get total issued return==//
                    $this->db->order_by('id','Asc');
                    $issue_qry = $this->db->select('id,qty,consume_qty')->get_where('issued_mgt',array('inventory_id' => $inventory_id, 'issued_return_for' => $issued_return_for));
                    if($issue_qry->num_rows()){
                        foreach($issue_qry->result() as $iss_rws){
                            $isqty = $iss_rws->qty-$iss_rws->consume_qty;
                            if($isqty>=$tot_issued_return){
                                $max_allow_qty = $isqty-$tot_issued_return;
                                $tot_issued_return = 0;
                            }
                            else{
                                $tot_issued_return = $tot_issued_return-$isqty;
                                $max_allow_qty = 0;
                            }
                            if($iss_rws->id==$val->id){
                                $consume_qty = $iss_rws->consume_qty;
                                break;
                            }
                        }
                    }

                }

            if($val->consume ==1){

                $sub_array[] = '';
            }
            elseif($max_allow_qty ==0){

                $sub_array[] = '<p style="color:blue;cursor:pointer;" onclick="open_consume_popup(' . $val->id . ');"><b>C</b></p>';

            }
            else{
                $sub_array[] = '<input type="checkbox" class="inventory_chkd" name="inv_checkbox" data-inventory-id ="'.$val->inventory_id.'" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'">';
            }
            $sub_array[] = $val->issued_no;
            $sub_array[] = $val->prod_name;
            $sub_array[] = $val->div_name;
            $sub_array[] = $val->cat_name;
            ;
            $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] = strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[] = strlen($val->inventory_grouping)<=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
            //$sub_array[] = $val->issued_type_name;
            if ($val->is_deleted == 0) {
                // $sub_array[] = '<span class="grn_qtys" id="inven_grn_qtys_' . $val->id . '" onclick="show_edit_issue_model(' . $val->id . ',' . $val->inventory_id . ')">' . $val->qty . '</span>';
                $sub_array[] = '<span>' . round($val->qty,2) . '</span>';
            } else {
                $sub_array[] = '<span>' . round($val->qty,2) . '(Deleted)</span>';
            }
            $sub_array[] = $val->uom_name;
            if ($val->issued_return_for == 0) {
                $tot_price_stock = ($val->qty) * round($val->stock_price,2);
                // $sub_array[] = formatIndianRupees(round($tot_price_stock,2));
                if($tot_price_stock>'1'){
                    $formatted_price = formatIndianRupees(round($tot_price_stock,2));
                }
                else{
                    $formatted_price = $tot_price_stock >= '.01' ? $tot_price_stock : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $sub_array[]=$formatted_price;
            } else {
                // $per_stock_price = isset($grn_issued_rate[$val->issued_return_for]) ? $grn_issued_rate[$val->issued_return_for] : 0;
                // $sub_array[] = ($val->qty) * ($per_stock_price);
                // $sub_array[] = ($val->qty) * ($grn_price_arr[$val->issued_return_for]);
                $tot_price_stock = ($val->qty) * round($grn_price_arr[$val->issued_return_for],2);
                // $sub_array[] = formatIndianRupees(round($tot_price_stock,2));
                if($tot_price_stock>'1'){
                    $formatted_price = formatIndianRupees(round($tot_price_stock,2));
                }
                else{
                    $formatted_price = $tot_price_stock >= '.01' ? $tot_price_stock : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $sub_array[]=$formatted_price;
            }


            $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
            // $sub_array[] =  $val->last_updated_date;
            $sub_array[] = date("d/m/Y", strtotime($val->last_updated_date));
            $sub_array[] = strlen($val->remarks)<=20 ? $val->remarks : substr($val->remarks,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->remarks.'"></i>';
            //$sub_array[] = strlen($val->issued_to)<=20 ? $val->issued_to : substr($val->issued_to,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->issued_to.'"></i>';
            if(is_numeric($val->issued_to)){
                $sub_array[] = isset($issue_to_list[$val->issued_to]) ? ucwords($issue_to_list[$val->issued_to]) : '';
            }
            else{
                $sub_array[] = isset($issue_to_list[$val->issued_to]) ? ucwords($issue_to_list[$val->issued_to]) : (strlen($val->issued_to)<=20 ? $val->issued_to : substr($val->issued_to,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->issued_to.'"></i>');
            }
            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }


    public function get_issued_report_data_old(){
        if($_POST['categorys'] != ''){
            $cat_id=array();
            $pre_qry = $this->db->select('id')->get_where('tbl_categories',array('name' => $_POST['categorys'], 'status' => 'Active', 'parent_id >' => '0'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->id]=$rowsss->id;
                }
            }
        }else{
            $cat_id = [];
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_issued_report_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_issued_report_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs    =   array();
        $totindqty  =   array();
        $no_inven_data = [];
        $inven_data = [];

        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1));

            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                }
            }
            foreach($totindqty as $key =>$val){
               $inven_data[] = $key;
            }
            foreach($invarrs as $in_id){
                if(!in_array($in_id,$inven_data)){
                    $no_inven_data[] = $in_id;
                }
            }
        }
        $div_arrs=array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',',$vals->category_ids);
            $div_arrs[$dcat_arr['0']]=$dcat_arr['0'];
            $div_arrs[$dcat_arr['1']]=$dcat_arr['1'];
        }
        $data1      =   [];
        $order      =   array();
        $unorder    =   array();
        if(isset($invarrs) && !empty($invarrs)){
            $this->db->where_in('inventory_id',$invarrs);
            $qrder_qry = $this->db->select("product_id,quantity,inventory_id")->get_where('order_sub_product',array('record_type' => 'Order', 'inv_status' => 1));
            //echo $this->db->last_query(); die;
            if($qrder_qry->num_rows()){
                $inscs_arr=array();
                foreach($qrder_qry->result() as $rdd){
                    if(!in_array($rdd->product_id,$inscs_arr)){
                        $inscs_arr[]=$rdd->product_id;
                        if(isset($order[$rdd->inventory_id])){
                            $order[$rdd->inventory_id]= $order[$rdd->inventory_id]+$rdd->quantity;
                        }
                        else{
                            $order[$rdd->inventory_id]= $rdd->quantity;
                        }
                    }
                }
            }

            $this->db->where_in('inventory_id',$invarrs);
            $uqrder_qry = $this->db->select("product_id,quantity,inventory_id")->get_where('order_sub_product',array('record_type' => 'Cart', 'inv_status' => 1));
            if($uqrder_qry->num_rows()){
                $inscs_arr_1=array();
                foreach($uqrder_qry->result() as $rdd){
                    if(!in_array($rdd->product_id,$inscs_arr_1)){
                        $inscs_arr_1[]=$rdd->product_id;
                        if(isset($unorder[$rdd->inventory_id])){
                            $unorder[$rdd->inventory_id]= $unorder[$rdd->inventory_id]+$rdd->quantity;
                        }
                        else{
                            $unorder[$rdd->inventory_id]= $rdd->quantity;
                        }
                    }
                }
            }
        }
        $order_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_ord_env = $this->db->select('SUM(quantity) AS total_quantity,MAX(inventory_id) as inventory_id')->get_where('tbl_order_confirmation_details',array('order_status' => '1', 'inv_status' => 1));
            if($qry_ord_env->num_rows()){
                foreach($qry_ord_env->result() as $res){
                    $order_inven_details[$res->inventory_id]    =   $res->total_quantity;
                }
            }
        }
        $total_order_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_total_ord_env = $this->db->select('SUM(quantity) AS total_quantity,MAX(inventory_id) as inventory_id')->get_where('tbl_order_confirmation_details',array('order_status' => '1'));
            if($qry_total_ord_env->num_rows()){
                foreach($qry_total_ord_env->result() as $res){
                    $total_order_inven_details[$res->inventory_id]    =   $res->total_quantity;
                }
            }
        }
        //=======GRN Qty===//
        $grn_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1));
            if($qry_grn_env->num_rows()){
                foreach($qry_grn_env->result() as $grn_res){
                    $grn_inven_details[$grn_res->inventory_id]    =   $grn_res->total_grn_quantity;
                }
            }
        }
        $total_grn_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt');
            if($qry_tot_grn_env->num_rows()){
                foreach($qry_tot_grn_env->result() as $grn_res){
                    $total_grn_inven_details[$grn_res->inventory_id]    =   $grn_res->total_grn_quantity;
                }
            }
        }
        //=======GRN Qty===//
        //=======Issued Qty===//
        $issued_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            //$qry_issued_env = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('inv_status' => 1));
            $qry_issued_env = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt');
            if($qry_issued_env->num_rows()){
                foreach($qry_issued_env->result() as $issue_res){
                    $issued_inven_details[$issue_res->inventory_id]    =   $issue_res->total_issued_quantity;
                }
            }
        }
        //=======Issued Qty===//
        foreach ($result as $key => $val) {
            $never_order = 0;
            $order_quan_count = 0;
            $tot_order_quan_count = 0;
            if(!in_array($val->id,$no_inven_data)){
                $never_order = 1;
            }
            if(isset($order_inven_details[$val->id])){
                $order_quan_count = $order_inven_details[$val->id];
            }
            if(isset($total_order_inven_details[$val->id])){
                $tot_order_quan_count=$total_order_inven_details[$val->id];
            }
            $grn_qty = 0;
            if(isset($grn_inven_details[$val->id])){
                $grn_qty = $grn_inven_details[$val->id];
            }
            $total_grn_qty = 0;
            if(isset($total_grn_inven_details[$val->id])){
                $total_grn_qty=$total_grn_inven_details[$val->id];
            }
            $issued_qty =   0;
            if(isset($issued_inven_details[$val->id])){
                $issued_qty = $issued_inven_details[$val->id];
            }

            $rfq_order_quantity = 0;
            $total_quantity = 0;
            $expdivcat = explode(',',$val->category_ids);
            $alldivcatnames = getCategorySubCategoryName_smt($div_arrs);
            $finldivcat['division_name']=isset($alldivcatnames[$expdivcat['0']]) ? $alldivcatnames[$expdivcat['0']] : '';
            $finldivcat['category_name']=isset($alldivcatnames[$expdivcat['1']]) ? $alldivcatnames[$expdivcat['1']] : '';
            $sub_array = array();
            //listing------
            $total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            $rfq_order_quantity = isset($order[$val->id]) ? $order[$val->id] : 0;

            $sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$val->id.'"  class="pr-3 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="close_issued_tds('.$val->id.')"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer" id="plus_'.$val->id.'" class="pr-3 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="open_issued_tds('.$val->id.')"><i class="bi bi-plus-lg"></i></span> <input type="checkbox" class="inventory_chkd" name="inv_checkbox" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'">';
            //$sub_array[] = $val->factory_name;
            $sub_array[] = $val->product_name;
            $sub_array[] = $finldivcat['division_name'];
            $sub_array[] = $finldivcat['category_name'];
            $sub_array[] = $val->specification;
            $sub_array[] = $val->size;
            $sub_array[] = $val->uom_name;
            $sub_array[] = $val->first_name; //$val->first_name.' '.$val->last_name;
            $sub_array[] = $val->opening_stock;
            if($tot_order_quan_count>0){
                $sub_array[] = ($val->opening_stock+$total_grn_qty)-($issued_qty);
            }
            else{
                $sub_array[] = '';
            }
            if($tot_order_quan_count>0){
                //$sub_array[] = '<span class="issued_qunty" id="grn_issued_quan_'.$val->id.'" onclick="show_issued_model('.$val->id.')">'.$issued_qty.'</span>';
                $sub_array[] = $issued_qty;
            }
            else{
                $sub_array[] = '0';
            }
            // $sub_array[] = '<span id="total_indent_qty_'.$val->id.'">'.$total_quantity.'</span>';
            // $sub_array[] = '<span class="active_rfq_quan" id="total_order_rfq_qty_'.$val->id.'" onclick="show_rfq_active('.$val->id.')" >'.$rfq_order_quantity.'</span>';
            // $sub_array[] = '<span class="active_order_quan" id="total_inven_orders_quan'.$val->id.'" onclick="show_order_model('.$val->id.')">'.$order_quan_count.'</span>';
            // if($order_quan_count>0){
            //     $sub_array[] = '<span class="grn_qunty" id="inven_grn_quan_'.$val->id.'" onclick="show_grn_model('.$val->id.')">'.$grn_qty.'</span>';
            // }
            // else{
            //     $sub_array[] = '0';
            // }
            $sub_array[] = '<span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            $data1[] = $sub_array;
        }
        // pr($data1); die;
        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  count($data1),
            "recordsFiltered"   =>  $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function fetch_all_issued_data(){
        if($this->input->is_ajax_request()){
            $inventory            =   $this->input->post('inventory');
            if($inventory){
                $this->db->select("issu.id,issu.inventory_id,issu.issued_no,issu.qty,issu.remarks,issu.last_updated_date,tusr.first_name,tusr.last_name", false);
                $this->db->from("issued_mgt as issu");
                $this->db->join("tbl_users as tusr",'tusr.id=issu.last_updated_by', 'LEFT');
                $this->db->where('issu.inventory_id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $res['status']          =   1;
                    $res['message']         =   'Issued Details found';
                    $res['resp']            =   $query->result();
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Issued Details not found';
                echo json_encode($res); die;
            }
        }
        else{
            $res['status']          =   2;
            $res['message']         =   'Issued not found';
            echo json_encode($res); die;
        }
    }
public function export_inventory(){
    $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs            =   array();
        $nonactiveindents   =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            //===Total Indent Qty ===//
            $this->db->where_in('inventory_id',$invarrs);
            $ind_qry = $this->db->select('inventory_id,indent_qty,is_active')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));
            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    if($inds_resp->is_active==1){
                        if(isset($totindqty[$inds_resp->inventory_id])){
                            $totindqty[$inds_resp->inventory_id]    =   $totindqty[$inds_resp->inventory_id]+round($inds_resp->indent_qty, 2);
                        }
                        else{
                        $totindqty[$inds_resp->inventory_id]    =   round($inds_resp->indent_qty, 2);
                        }
                    }
                    else{
                        $nonactiveindents[$inds_resp->inventory_id] =   $inds_resp->inventory_id;
                    }
                }
            }
            //===Total Indent Qty ===//

        }

        $data1      =   [];
        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $already_fetch_rfq              =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            //$rfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){
                foreach($rfq_qry->result() as $rfq_rows){
                    $already_fetch_rfq[$rfq_rows->id]   =   $rfq_rows->id;
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //pr($already_fetch_rfq); die;
            if(isset($already_fetch_rfq) && !empty($already_fetch_rfq)){
                $this->db->group_by('variant_grp_id');
                $this->db->where_in('inventory_id',$invarrs);
                $this->db->where_not_in('id',$already_fetch_rfq);
                $this->db->where_in('buyer_rfq_status',array('8','10'));
                $arf_rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                if($arf_rfq_qry->num_rows()){
                    foreach($arf_rfq_qry->result() as $rfq_rows){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }
                }
            }
            //pr($close_rfq_id_arr); die;
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            //pr($close_rfq_id_arr); die;
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            //pr($close_price_ids); die;
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    //pr($qry_rfq_order->result()); die;
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    //pr($closed_order); die;
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //pr($final_close_order); die;
            //===Closed RFQ Qty=====//
            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //===GRN====//
            $new_grn_wpo_arr    =   array();
            $grn_manual_po_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->where_in('grn_type',array('1','4'));
            $new_qry_grn_wp_mpo = $this->db->select('grn_qty,inventory_id,grn_type')->get_where('grn_mgt',array('inv_status' => 1, 'is_deleted' => '0'));
            // $new_qry_grn_wp_mpo = $this->db->select('grn_qty,inventory_id,grn_type')->get_where('grn_mgt',array('inv_status' => 0, 'is_deleted' => '0'));
            if($new_qry_grn_wp_mpo->num_rows()){
                foreach($new_qry_grn_wp_mpo->result() as $grn_wp_mpo_res){
                    if($grn_wp_mpo_res->grn_type==1){
                        if(isset($new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id])){
                            $new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id] = $new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id]+$grn_wp_mpo_res->grn_qty;
                        }else{
                            $new_grn_wpo_arr[$grn_wp_mpo_res->inventory_id] = $grn_wp_mpo_res->grn_qty;
                        }
                    }
                    // if($grn_wp_mpo_res->grn_type==4){
                    //     if(isset($grn_manual_po_arr[$grn_wp_mpo_res->inventory_id])){
                    //         $grn_manual_po_arr[$grn_wp_mpo_res->inventory_id] = $grn_manual_po_arr[$grn_wp_mpo_res->inventory_id]+$grn_wp_mpo_res->grn_qty;
                    //     }else{
                    //         $grn_manual_po_arr[$grn_wp_mpo_res->inventory_id] = $grn_wp_mpo_res->grn_qty;
                    //     }
                    // }
                }
            }

            /*comment by sumit on 30-12-24--
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN MANUAL PO====//
            $grn_manual_po_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_manual_po = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'inv_status' => 1,'grn_type' => '4', 'is_deleted' => '0'));
            if($qry_grn_manual_po->num_rows()){
                foreach($qry_grn_manual_po->result() as $grn_manual_po_res){
                    $grn_manual_po_arr[$grn_manual_po_res->inventory_id]    =   $grn_manual_po_res->total_grn_quantity;
                }
            }*/
            //===GRN MANUAL PO====//
            //===GRN WPO====//

            $grn_wpo_arr    =   array();
            $grn_mpo_arr    =   array();
            $grn_wopo_arr   =   array();
            $grn_stock_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->where_in('grn_type',array('1','2','3','4'));
            $qry_grn_wp_wpo = $this->db->select('grn_qty,inventory_id,grn_type')->get_where('grn_mgt',array('is_deleted' => '0'));
            if($qry_grn_wp_wpo->num_rows()){
                foreach($qry_grn_wp_wpo->result() as $grn_wp_wop_res){
                    if($grn_wp_wop_res->grn_type==1){
                        if(isset($grn_wpo_arr[$grn_wp_wop_res->inventory_id])){
                            $grn_wpo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wpo_arr[$grn_wp_wop_res->inventory_id]+$grn_wp_wop_res->grn_qty;
                        }
                        else{
                            $grn_wpo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wp_wop_res->grn_qty;
                        }
                    }
                    if($grn_wp_wop_res->grn_type==4){
                        if(isset($grn_mpo_arr[$grn_wp_wop_res->inventory_id])){
                            $grn_mpo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_mpo_arr[$grn_wp_wop_res->inventory_id]+$grn_wp_wop_res->grn_qty;
                        }
                        else{
                            $grn_mpo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wp_wop_res->grn_qty;
                        }
                    }
                    if($grn_wp_wop_res->grn_type==2){
                        if(isset($grn_wopo_arr[$grn_wp_wop_res->inventory_id])){
                            $grn_wopo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wp_wop_res->inventory_id]+$grn_wp_wop_res->grn_qty;
                        }
                        else{
                            $grn_wopo_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wp_wop_res->grn_qty;
                        }
                    }
                    if($grn_wp_wop_res->grn_type==3){
                        if(isset($grn_stock_arr[$grn_wp_wop_res->inventory_id])){
                            $grn_stock_arr[$grn_wp_wop_res->inventory_id]    =   $grn_stock_arr[$grn_wp_wop_res->inventory_id]+$grn_wp_wop_res->grn_qty;
                        }
                        else{
                            $grn_stock_arr[$grn_wp_wop_res->inventory_id]    =   $grn_wp_wop_res->grn_qty;
                        }
                    }
                }
            }




            /*code comment by sumit on 30dec24--
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//
            $grn_wopo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->total_grn_quantity;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->total_grn_quantity;
                }
            }--*/
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            $qry_issued = $this->db->select('qty,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    if(isset($issued_arr[$issue_res->inventory_id])){
                        $issued_arr[$issue_res->inventory_id]    =   $issued_arr[$issue_res->inventory_id]+$issue_res->qty;
                    }
                    else{
                        $issued_arr[$issue_res->inventory_id]    =   $issue_res->qty;
                    }
                }
            }
            //===Issued===//

            //====Issued Return===//
            $issued_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            $qry_issued_return = $this->db->select('qty,inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    if($issued_return_arr[$issue_ret_res->inventory_id]){
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   $issued_return_arr[$issue_ret_res->inventory_id]+$issue_ret_res->qty;
                    }else{
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->qty;
                    }
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(inventory_id) as inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            $qry_stock_return = $this->db->select('qty,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    if(isset($stock_return_arr[$stock_ret_res->inventory_id])){
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_return_arr[$stock_ret_res->inventory_id]+$stock_ret_res->qty;
                    }else{
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty;
                    }
                }
            }
            //===Stock Return=====//
            //===get manual po details==//
            $mpo_datas_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $tmpo_qry   =   $this->db->select('inventory_id')->get_where('tbl_manual_po_order',array('order_status' => '1'));
            if($tmpo_qry->num_rows()){
                foreach($tmpo_qry->result() as $tmpo_vals){
                    $mpo_datas_arr[$tmpo_vals->inventory_id] = $tmpo_vals->inventory_id;
                }
            }
        }
            // pr($new_grn_wpo_arr);
            // pr($grn_qty);die;
        // pr($result);
        $final_data = array();
        $i          =   0;
        foreach ($result as $key => $val) {
            //===Indent Qty==//
           $total_quantity = isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            ///   new  grn /////
            $new_grn_qty = 0;
            if(isset($new_grn_wpo_arr[$val->id])){
                $new_grn_qty = $new_grn_wpo_arr[$val->id];
            }
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_mpo = 0;
            if(isset($grn_mpo_arr[$val->id])){
                $grn_qty_mpo = $grn_mpo_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }
            //manual po
            $grn_qty_manual_po = 0;
            if(isset($grn_manual_po_arr[$val->id])){
                $grn_qty_manual_po = $grn_manual_po_arr[$val->id];
            }
            //manual po
            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            //===Stock Return====//

            //=====mpo_datas===//
            $mpo_datas_qty = 0;
            if(isset($mpo_datas_arr[$val->id])){
                $mpo_datas_qty = $mpo_datas_arr[$val->id];
            }
            //====mpo_datas====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock    =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty+$grn_qty_mpo)-($issued_qty+$stock_return_qty);


            //$sub_array[] = '<span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            $final_data[$i]['Branch']               =   $val->factory_name;
            $final_data[$i]['Product']              =   $val->prod_name;
            $final_data[$i]['cat_name']             =   $val->cat_name;
            $final_data[$i]['buyer_product_name']   =   $val->buyer_product_name;
            $final_data[$i]['Specification']        =   _sanetiz_all_string_data($val->specification,'decode');
            $final_data[$i]['Size']                 =   _sanetiz_all_string_data($val->size,'decode');
            $final_data[$i]['brand']                =   $val->product_brand;
            $final_data[$i]['grouping']             =   $val->inventory_grouping;
            $final_data[$i]['Current Stock']        =   round($mystock,2);
            $final_data[$i]['UOM']                  =   $val->uom_name;
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty = $issued_qty-$issued_return_qty;
                //$final_data[$i]['Issued']    =   round($orgnal_issued_qty,2);
            }
            else{
                //$final_data[$i]['Issued']    =   0;
            }
            $final_data[$i]['Indent Qty']   =   isset($total_quantity) ? round($total_quantity,2) : 0;

            if($total_RFQ>0){
                $final_data[$i]['RFQ Qty']      = round($total_RFQ,2);
            }else{
                $final_data[$i]['RFQ Qty']      = '0';
            }

            if($totl_order>0){
                $final_data[$i]['Order Qty']      = round($totl_order,2);
            }else{
                $final_data[$i]['Order Qty']      = '0';
            }
            if($totl_order>0){
                $final_data[$i]['grn Qty']      = round($new_grn_qty,2);
            }else{
                $final_data[$i]['grn Qty']      = '0';
            }
             $i++;

        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
        // pr($data1); die;
        // $output = array(
        //     "draw"              =>  intval($_POST["draw"]),
        //     "recordsTotal"      =>  count($data1),
        //     "recordsFiltered"   =>  $total_record,
        //     "data" => $data1
        // );
        // // pr($output); die;
        // echo json_encode($output);
}
    public function export_inventory_old(){
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs    =   array();

        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            //===Total Indent Qty ===//
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));

            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                }
            }
            //===Total Indent Qty ===//

        }

        $data1      =   [];
        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){

                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();

            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }

            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//
            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }//===GRN MANUAL PO====//
            $grn_manual_po_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_manual_po = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '4', 'is_deleted' => '0'));
            if($qry_grn_manual_po->num_rows()){
                foreach($qry_grn_manual_po->result() as $grn_manual_po_res){
                    $grn_manual_po_arr[$grn_manual_po_res->inventory_id]    =   $grn_manual_po_res->total_grn_quantity;
                }
            }
            //===GRN MANUAL PO====//
            //===GRN WPO====//
            $grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//
            $grn_wopo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->total_grn_quantity;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->total_grn_quantity;
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    $issued_arr[$issue_res->inventory_id]    =   $issue_res->total_issued_quantity;
                }
            }
            //===Issued===//

            //====Issued Return===//
            $issued_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->total_ir_quantity;
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(inventory_id) as inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->total_stock_return;
                }
            }
            //===Stock Return=====//
        }
            // pr($new_grn_wpo_arr);die;
        // pr($issued_return_arr);
         $data = array();
         $i = 0;
         $final_data = array();
        foreach ($result as $key => $val) {
            //===Indent Qty==//
            $total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            ///   new  grn /////
            $new_grn_qty = 0;
            if(isset($new_grn_wpo_arr[$val->id])){
                $new_grn_qty = $new_grn_wpo_arr[$val->id];
            }
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }
            //manual po
            $grn_qty_manual_po = 0;
            if(isset($grn_manual_po_arr[$val->id])){
                $grn_qty_manual_po = $grn_manual_po_arr[$val->id];
            }
            //manual po

            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock    =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty+$grn_qty_manual_po)-($issued_qty+$stock_return_qty);
            $is_del_invs     =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="1">';
            if($val->opening_stock>=1 || $val->is_indent==1){
                $is_del_invs =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="0">';
            }
            $sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$val->id.'"  class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="close_indent_tds('.$val->id.')"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer" id="plus_'.$val->id.'" class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="open_indent_tds('.$val->id.')"><i class="bi bi-plus-lg"></i></span> <input type="checkbox" class="inventory_chkd" name="inv_checkbox" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'">';
            //$sub_array[] = $val->factory_name;
            if(isset($val->indent_min_qty) && $val->indent_min_qty>0&& $mystock<=$val->indent_min_qty){
                $sub_array[] = $val->product_name.'<button type="button" class="btn  position-relative" style="color:white !important; background: #015294 !important; border-color:#015294 !important;">Min Qty<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill " style="background:red!important; color: white !important; padding-left:2px!impotant;">'.$val->indent_min_qty.'</span></button>';
            }
            else{
                $sub_array[] = $val->prod_name;
            }
            $sub_array[] = $val->cat_name;
            $sub_array[] = $val->buyer_product_name;
            //$sub_array[] = $finldivcat['division_name'].'/'.$finldivcat['category_name'];
            //$sub_array[] = $finldivcat['category_name'];
            $sub_array[] = $val->specification;
            $sub_array[] = $val->size.$is_del_invs;
            $sub_array[] = $val->uom_name.'<input type="hidden" id="inventory_addedby_name_'.$val->id.'" value="'.$val->first_name.'">';

            $sub_array[] = $val->inventory_grouping;
            //$sub_array[] = $val->opening_stock;
            $sub_array[] = round($mystock,2);
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty = $issued_qty-$issued_return_qty;
                $sub_array[] = '<span class="issued_qunty" id="grn_issued_quan_'.$val->id.'" onclick="show_issued_model('.$val->id.')">'.round($orgnal_issued_qty,2).'</span>';
            }
            else{
                $sub_array[] = '0';
            }
            $sub_array[] = '<span id="total_indent_qty_'.$val->id.'">'.round($total_quantity,2).'</span>';
            if($total_RFQ>0){
                $sub_array[] = '<span class="active_rfq_quan" id="total_order_rfq_qty_'.$val->id.'" onclick="show_rfq_active('.$val->id.')" >'.round($total_RFQ,2).'</span>';
            }
            else{
                $sub_array[] = '0';
            }
            if($totl_order>0){
                $sub_array[] = '<span class="active_order_quan" id="total_inven_orders_quan'.$val->id.'" onclick="show_order_model('.$val->id.')">'.round($totl_order,2).'</span>';
            }
            else{
                $sub_array[] = '0';
            }
            if($totl_order>0){
                $sub_array[] = '<span class="grn_qunty" id="inven_grn_quan_'.$val->id.'" onclick="show_grn_model('.$val->id.')">'.round($grn_qty,2).'</span> <span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            }
            else{
                $sub_array[] = '<span class="grn_qunty" id="inven_grn_quan_'.$val->id.'" onclick="show_grn_model('.$val->id.')">'.round($grn_qty,2).'</span> <span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
                //$sub_array[] = '<span class="grn_qunty" id="inven_grn_quan_'.$val->id.'" onclick="grn_without_po_details_model('.$val->id.')">0</span> <span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            }
            //$sub_array[] = '<span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            $data1[] = $sub_array;
            $final_data[$i]['Branch']               =   $val->factory_name;
            $final_data[$i]['Product']              =   $val->prod_name;//$val->product_name;
            $final_data[$i]['cat_name']             =   $val->cat_name;
            $final_data[$i]['buyer_product_name']   =   $val->buyer_product_name;
            $final_data[$i]['Specification']        =   $val->specification;
            $final_data[$i]['Size']                 =   $val->size;//$val->size;
            $final_data[$i]['brand']                =   $val->product_brand;
            $final_data[$i]['grouping']             =  $val->inventory_grouping;
            $final_data[$i]['Current Stock']        =   round($mystock,2);
            $final_data[$i]['UOM']                  =   $val->uom_name;
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty = $issued_qty-$issued_return_qty;
                //$final_data[$i]['Issued']    =   round($orgnal_issued_qty,2);
            }
            else{
                $final_data[$i]['Issued']    =   0;
            }
            $final_data[$i]['Indent Qty']   =   isset($total_quantity) ? round($total_quantity,2) : 0;

             if($total_RFQ>0){
                $final_data[$i]['RFQ Qty']      = round($total_RFQ,2);
             }else{
                $final_data[$i]['RFQ Qty']      = '0';
             }

             if($totl_order>0){
                $final_data[$i]['Order Qty']      = round($totl_order,2);
             }else{
                $final_data[$i]['Order Qty']      = '0';
             }
             if($totl_order>0){
                $final_data[$i]['grn Qty']      = round($new_grn_qty,2);
             }else{
                $final_data[$i]['grn Qty']      = '0';
             }
            // $final_data[$i]['RFQ Qty']      =   isset($total_RFQ) ? $total_RFQ : 0;
            // $final_data[$i]['Order Qty']    =   isset($totl_order) ? $totl_order : 0;
            // $final_data[$i]['GRN Qty']      =   isset($grn_qty) ? $grn_qty : 0;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }


    public function export_issued_inventory()
    {
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_issued_report_data_new($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_issued_report_data_new($users_ids, $buyer_users, 'total',$cat_id);
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];

        $data1 = array();
        $issued_return_for_arr = array();
        $invarrs        =   array();
        $grn_price_arr  =   array();
        foreach ($result as $key => $vals) {
            $invarrs[$vals->inventory_id]               =   $vals->inventory_id;
            $grn_price_arr['os'][$vals->inventory_id]   =   $vals->stock_price;
            //$issued_return_for_arr[$vals->issued_return_for] = $vals->issued_return_for;
        }
        if(isset($invarrs) && !empty($invarrs)){
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number][$rp_row->inventory_id] = $rp_row->order_price;
                }
            }
            //====wpo price===//
            //====manual po price===//
            $manualpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('tbl_manual_po_order',array('product_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $manualpo_price[$rp_row->manual_po_number][$rp_row->inventory_id] = $rp_row->product_price;
                }
            }
            //====manual po price===//

            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    }
                    else{
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    }
                    $grn_price_arr[$grn_wp_res->id] =   isset($grn_wp_res->grn_buyer_rate) && $grn_wp_res->grn_buyer_rate>0 ? $grn_wp_res->grn_buyer_rate : $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                }
            }
            //pr($grn_wpo_arr); die;
            //===GRN WPO====//
            //===GRN manualPO====//
            $grn_manualpo_arr        =   array();
            $grn_manualpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_manualp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')->get_where('grn_mgt',array('grn_type' => '4', 'is_deleted' => '0'));
            if($qry_grn_manualp->num_rows()){
                foreach($qry_grn_manualp->result() as $grn_manualp_res){
                    if(isset($grn_manualpo_arr[$grn_manualp_res->inventory_id])){
                        $grn_manualpo_arr[$grn_manualp_res->inventory_id]    =  $grn_manualpo_arr[$grn_manualp_res->inventory_id] + $grn_manualp_res->grn_qty;
                    }
                    else{
                        $grn_manualpo_arr[$grn_manualp_res->inventory_id]    =   $grn_manualp_res->grn_qty;
                    }
                    if(isset($grn_manualpo_price_arr[$grn_manualp_res->inventory_id])){
                        $grn_manualpo_price_arr[$grn_manualp_res->inventory_id]    =  $grn_manualpo_price_arr[$grn_manualp_res->inventory_id] + $grn_manualp_res->grn_qty*$manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                    }
                    else{
                        $grn_manualpo_price_arr[$grn_manualp_res->inventory_id]    =   $grn_manualp_res->grn_qty*$manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                    }
                    $grn_price_arr[$grn_manualp_res->id] =   isset($grn_manualp_res->grn_buyer_rate) && $grn_manualp_res->grn_buyer_rate>0 ? $grn_manualp_res->grn_buyer_rate : $manualpo_price[$grn_manualp_res->po_number][$grn_manualp_res->inventory_id];
                }
            }
            //pr($grn_wpo_arr); die;
            //===GRN manualPO====//


            //===GRN WOPO====//
            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*($grn_wop_res->rate));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*($grn_wop_res->rate);
                    }
                    $grn_price_arr[$grn_wop_res->id] =   $grn_wop_res->rate;
                }
            }

            //===GRN WOPO====//
        }
        //pr($grn_price_arr); die;
        //====Get all grn  value====//
        // $grn_issued_rate = array();
        // $all_po_number = array();
        // $grn_po_number = array();
        // if (isset($issued_return_for_arr) && !empty($issued_return_for_arr)) {
        //     $this->db->where_in('id', $issued_return_for_arr);
        //     $grn_rate_qry = $this->db->select('id,rate,grn_type,po_number')->get_where('grn_mgt', array());
        //     if ($grn_rate_qry->num_rows()) {
        //         foreach ($grn_rate_qry->result() as $grn_row) {
        //             if ($grn_row->grn_type == 1) {
        //                 $grn_po_number[$grn_row->po_number] = $grn_row->id;
        //                 $all_po_number[] = $grn_row->po_number;
        //             } else {
        //                 $grn_issued_rate[$grn_row->id] = $grn_row->rate;
        //             }
        //         }

        //     }
        // }
        //====Get all grn  value====//
        $sr_no = 1;
        // pr($result);die;
        //====Issue to data====//
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $issue_to_list = array();
        $issue_to_qry = $this->db->get_where('issue_to_mgt',array('user_id' => $user_id));
        if($issue_to_qry->num_rows()){
            foreach($issue_to_qry->result() as $itdrow){
                $issue_to_list[$itdrow->id]    =   $itdrow->name;
            }
        }
        //===issue to data====//
        $sr_no = 1;
        $i=0;
        // pr($result);die;
        foreach ($result as $key => $val) {
            $final_data[$i]['Issued Number']    =   $val->issued_no;
            $final_data[$i]['Product']          =   $val->prod_name;
            $final_data[$i]['Division']         =   $val->div_name;
            $final_data[$i]['Category']         =   $val->cat_name;
            $final_data[$i]['Specification']    =   ($val->specification);
            $final_data[$i]['Size']             =   ($val->size);
            $final_data[$i]['grp']              =   ($val->inventory_grouping);
            //$final_data[$i]['issued_typ'] = $val->issued_type_name;
            if ($val->is_deleted == 0) {
                $final_data[$i]['Issued Qty'] = round($val->qty,2);
            }else{
                 $final_data[$i]['Issued Qty'] = round($val->qty,2). ' (Deleted)';
            }
            $final_data[$i]['UOM'] = $val->uom_name;
            if ($val->issued_return_for == 0) {
                $tot_price_stock = ($val->qty) * round($val->stock_price,2);
                // $sub_array[] = formatIndianRupees(round($tot_price_stock,2));
                if($tot_price_stock>'1'){
                    $formatted_price = formatIndianRupees(round($tot_price_stock,2));
                }
                else{
                    $formatted_price = $tot_price_stock >= '.01' ? $tot_price_stock : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['Amount']=$formatted_price;
            } else {
                // $per_stock_price = isset($grn_issued_rate[$val->issued_return_for]) ? $grn_issued_rate[$val->issued_return_for] : 0;
                // $sub_array[] = ($val->qty) * ($per_stock_price);
                // $sub_array[] = ($val->qty) * ($grn_price_arr[$val->issued_return_for]);
                $tot_price_stock = ($val->qty) * round($grn_price_arr[$val->issued_return_for],2);
                // $sub_array[] = formatIndianRupees(round($tot_price_stock,2));
                if($tot_price_stock>'1'){
                    $formatted_price = formatIndianRupees(round($tot_price_stock,2));
                }
                else{
                    $formatted_price = $tot_price_stock >= '.01' ? $tot_price_stock : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['Amount']=$formatted_price;
            }

            $final_data[$i]['Added BY']     =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date']   =   date('Y-m-d',strtotime($val->last_updated_date));
            $final_data[$i]['Remarks']      =   HtmlDecodeString($val->remarks);
            //$final_data[$i]['Issued To'] = $val->issued_to;
            if(is_numeric($val->issued_to)){
                $final_data[$i]['Issued To'] = isset($issue_to_list[$val->issued_to]) ? ucwords($issue_to_list[$val->issued_to]) : '';
            }
            else{
                $final_data[$i]['Issued To'] = isset($issue_to_list[$val->issued_to]) ? ucwords($issue_to_list[$val->issued_to]) : (strlen($val->issued_to)<=20 ? $val->issued_to : substr($val->issued_to,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->issued_to.'"></i>');
            }
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function export_issued_inventory_old(){
        if($_POST['categorys'] != ''){
            $cat_id=array();
            $pre_qry = $this->db->select('id')->get_where('tbl_categories',array('name' => $_POST['categorys'], 'status' => 'Active', 'parent_id >' => '0'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->id]=$rowsss->id;
                }
            }
        }else{
            $cat_id = [];
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_issued_report_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_issued_report_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs    =   array();
        $totindqty  =   array();
        $no_inven_data = [];
        $inven_data = [];

        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1));

            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                }
            }
            foreach($totindqty as $key =>$val){
               $inven_data[] = $key;
            }
            foreach($invarrs as $in_id){
                if(!in_array($in_id,$inven_data)){
                    $no_inven_data[] = $in_id;
                }
            }
        }
        $div_arrs=array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',',$vals->category_ids);
            $div_arrs[$dcat_arr['0']]=$dcat_arr['0'];
            $div_arrs[$dcat_arr['1']]=$dcat_arr['1'];
        }
        $data1      =   [];
        $order      =   array();
        $unorder    =   array();
        if(isset($invarrs) && !empty($invarrs)){
            $this->db->where_in('inventory_id',$invarrs);
            $qrder_qry = $this->db->select("product_id,quantity,inventory_id")->get_where('order_sub_product',array('record_type' => 'Order', 'inv_status' => 1));
            //echo $this->db->last_query(); die;
            if($qrder_qry->num_rows()){
                $inscs_arr=array();
                foreach($qrder_qry->result() as $rdd){
                    if(!in_array($rdd->product_id,$inscs_arr)){
                        $inscs_arr[]=$rdd->product_id;
                        if(isset($order[$rdd->inventory_id])){
                            $order[$rdd->inventory_id]= $order[$rdd->inventory_id]+$rdd->quantity;
                        }
                        else{
                            $order[$rdd->inventory_id]= $rdd->quantity;
                        }
                    }
                }
            }

            $this->db->where_in('inventory_id',$invarrs);
            $uqrder_qry = $this->db->select("product_id,quantity,inventory_id")->get_where('order_sub_product',array('record_type' => 'Cart', 'inv_status' => 1));
            if($uqrder_qry->num_rows()){
                $inscs_arr_1=array();
                foreach($uqrder_qry->result() as $rdd){
                    if(!in_array($rdd->product_id,$inscs_arr_1)){
                        $inscs_arr_1[]=$rdd->product_id;
                        if(isset($unorder[$rdd->inventory_id])){
                            $unorder[$rdd->inventory_id]= $unorder[$rdd->inventory_id]+$rdd->quantity;
                        }
                        else{
                            $unorder[$rdd->inventory_id]= $rdd->quantity;
                        }
                    }
                }
            }
        }
        $order_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_ord_env = $this->db->select('SUM(quantity) AS total_quantity,MAX(inventory_id) as inventory_id')->get_where('tbl_order_confirmation_details',array('order_status' => '1', 'inv_status' => 1));
            if($qry_ord_env->num_rows()){
                foreach($qry_ord_env->result() as $res){
                    $order_inven_details[$res->inventory_id]    =   $res->total_quantity;
                }
            }
        }
        $total_order_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_total_ord_env = $this->db->select('SUM(quantity) AS total_quantity,MAX(inventory_id) as inventory_id')->get_where('tbl_order_confirmation_details',array('order_status' => '1'));
            if($qry_total_ord_env->num_rows()){
                foreach($qry_total_ord_env->result() as $res){
                    $total_order_inven_details[$res->inventory_id]    =   $res->total_quantity;
                }
            }
        }
        //=======GRN Qty===//
        $grn_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1));
            if($qry_grn_env->num_rows()){
                foreach($qry_grn_env->result() as $grn_res){
                    $grn_inven_details[$grn_res->inventory_id]    =   $grn_res->total_grn_quantity;
                }
            }
        }
        $total_grn_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt');
            if($qry_tot_grn_env->num_rows()){
                foreach($qry_tot_grn_env->result() as $grn_res){
                    $total_grn_inven_details[$grn_res->inventory_id]    =   $grn_res->total_grn_quantity;
                }
            }
        }
        //=======GRN Qty===//
        //=======Issued Qty===//
        $issued_inven_details = [];
        if(!empty($result)){
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            //$qry_issued_env = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('inv_status' => 1));
            $qry_issued_env = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt');
            if($qry_issued_env->num_rows()){
                foreach($qry_issued_env->result() as $issue_res){
                    $issued_inven_details[$issue_res->inventory_id]    =   $issue_res->total_issued_quantity;
                }
            }
        }
        //=======Issued Qty===//
        $final_data =   array();
        $i          =   0;
        foreach ($result as $key => $val) {
            $never_order = 0;
            $order_quan_count = 0;
            $tot_order_quan_count = 0;
            if(!in_array($val->id,$no_inven_data)){
                $never_order = 1;
            }
            if(isset($order_inven_details[$val->id])){
                $order_quan_count = $order_inven_details[$val->id];
            }
            if(isset($total_order_inven_details[$val->id])){
                $tot_order_quan_count=$total_order_inven_details[$val->id];
            }
            $grn_qty = 0;
            if(isset($grn_inven_details[$val->id])){
                $grn_qty = $grn_inven_details[$val->id];
            }
            $total_grn_qty = 0;
            if(isset($total_grn_inven_details[$val->id])){
                $total_grn_qty=$total_grn_inven_details[$val->id];
            }
            $issued_qty =   0;
            if(isset($issued_inven_details[$val->id])){
                $issued_qty = $issued_inven_details[$val->id];
            }

            $rfq_order_quantity = 0;
            $total_quantity = 0;
            $expdivcat = explode(',',$val->category_ids);
            $alldivcatnames = getCategorySubCategoryName_smt($div_arrs);
            $finldivcat['division_name']=isset($alldivcatnames[$expdivcat['0']]) ? $alldivcatnames[$expdivcat['0']] : '';
            $finldivcat['category_name']=isset($alldivcatnames[$expdivcat['1']]) ? $alldivcatnames[$expdivcat['1']] : '';
            $sub_array = array();
            //listing------
            $total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            $rfq_order_quantity = isset($order[$val->id]) ? $order[$val->id] : 0;


            $final_data[$i]['Branch']           =   $val->factory_name;
            $final_data[$i]['Product']          =   $val->product_name;
            $final_data[$i]['Division']         =   $finldivcat['division_name'];
            $final_data[$i]['Category']         =   $finldivcat['category_name'];
            $final_data[$i]['Specification']    =   $val->specification;
            $final_data[$i]['Size']             =   $val->size;
            $final_data[$i]['UOM']              =   $val->uom_name;
            $final_data[$i]['Added BY']         =   $val->first_name; //$val->first_name.' '.$val->last_name;
            $final_data[$i]['Orig Stock']       =   $val->opening_stock;
            if($tot_order_quan_count>0){
                $final_data[$i]['Current Stock']    =   ($val->opening_stock+$total_grn_qty)-($issued_qty);
            }
            else{
                $final_data[$i]['Current Stock']    =  0;
            }
            if($tot_order_quan_count>0){
                $final_data[$i]['Issued']       =   $issued_qty;
            }
            else{
                $final_data[$i]['Issued']       =   0;
            }
            // $final_data[$i]['Indent Qty']       =   $total_quantity;
            // $final_data[$i]['RFQ Qty']          =   $rfq_order_quantity;
            // $final_data[$i]['Order Qty']        =   $order_quan_count;
            // $final_data[$i]['GRN QtY']          =   $grn_qty;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }
    public function grn_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "GRN Report Management";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        $data['currency_list']      =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']     =   $buyer_currency;
        $this->load->view('inventory_management/grn_report_list',$data);
    }
    public function stock_return_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
            $this->db->select("first_name, last_name,id");
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query1 = $this->db->get();
            if ($query1->num_rows() > 0) {
                $data['user_name']= $query1->row()->first_name." ".$query1->row()->last_name;
                $data['user_id']= $query1->row()->id;
            }
        } else {
            $user_id   =  $users['users_id'];

            $data['user_id']=$user_id;
            $this->db->select("first_name, last_name");
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query1 = $this->db->get();
            if ($query1->num_rows() > 0) {
                $data['user_name']= $query1->row()->first_name." ".$query1->row()->last_name;
            }
        }
        $data['page_title']         =   "Stock Return Report Management";
        //$user_id                  =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $qry_invt_type    =   $this->db->select('id,name')->get_where('issued_type',array('status' => '1'));
         if($qry_invt_type->num_rows()){
            $data['invt_type'] = $qry_invt_type->result();
         }
        $this->load->view('inventory_management/stock_return_report_list',$data);
    }
    public function issued_return_reports(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
            $this->db->select("first_name, last_name,id");
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query1 = $this->db->get();
            if ($query1->num_rows() > 0) {
                $data['user_name']= $query1->row()->first_name." ".$query1->row()->last_name;
                $data['user_id']= $query1->row()->id;
            }
        } else {
            $user_id   =  $users['users_id'];
            $data['user_id']=$user_id;
            $this->db->select('first_name, last_name');
            $this->db->from('tbl_users');
            $this->db->where('id', $user_id);
            $query = $this->db->get();
            if ($query->num_rows() > 0) {
                $data['user_name']= $query->row()->first_name." ".$query->row()->last_name;
            }
        }
        $data['page_title']         =   "Issued Return Report List";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $this->load->view('inventory_management/issued_return_reports',$data);
    }
    public function stock_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Stock Report List";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        $data['currency_list']      =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']     =   $buyer_currency;
        $this->load->view('inventory_management/stock_report_list',$data);
    }

    public function get_stock_return_report_data(){
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_stock_return_report_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_stock_return_report_data($users_ids, $buyer_users,'total',$cat_id);
        // print_r($result);
        // pr($result); die;
        $sr_no  =   1;
        $data1  =   array();
        foreach ($result as $key => $val) {

            $sub_array = array();
            //listing------
            // $sub_array[] = $sr_no;
            $sub_array[] = $val->stock_no;
            $sub_array[] = $val->prod_name;
            $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] = strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[] = strlen($val->inventory_grouping)<=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
            $sub_array[] = $val->stock_type;
            $sub_array[] = $val->first_name; //$val->first_name.' '.$val->last_name;
            $sub_array[] = strlen($val->stock_vendor_name)<=20 ? $val->stock_vendor_name : substr($val->stock_vendor_name,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->stock_vendor_name.'"></i>';;
            $sub_array[] = date("d/m/Y", strtotime($val->last_updated_date));
            if($val->is_deleted==0){
                if($val->stock_return_type == 1){

                 $sub_array[] = round($val->qty,2);
                }else{

                $sub_array[] = '<span class="grn_qtys" id="inven_grn_qtys_'.$val->id.'" onclick="show_edit_stock_return_model('.$val->id.','.$val->inventory_id.')">'.round($val->qty,2).'</span>';
                }
            }
            else{
                $sub_array[] = '<span >'.round($val->qty,2).'(Deleted)</span>';
            }
            $sub_array[] = $val->uom_name;
            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  count($data1),
            "recordsFiltered"   =>  $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function get_issued_return_report_data()
    {
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_issued_return_report_data($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_issued_return_report_data($users_ids, $buyer_users, 'total',$cat_id);
        $issued_return_type = array();
        $qrys = $this->db->get('issued_type');
        if($qrys->num_rows() > 0){
            foreach($qrys->result() as $v){
                $issued_return_type[$v->id] = $v->name;
            }
        }
        $data1 = array();

        $sr_no = 1;
        $data1 = array();
        // pr($result);die;
        foreach ($result as $key => $val) {
            $sub_array = array();
            //listing------

            $sub_array[] =  $val->issued_return_no;
            $sub_array[] =  $val->prod_name;
            $sub_array[] =  strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] =  strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[] =  strlen($val->inventory_grouping)<=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
            $sub_array[] =  $issued_return_type[$val->issued_return_type];
            $sub_array[] =  $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $sub_array[] =  date("d/m/Y", strtotime($val->last_updated_date));
            $sub_array[] =  round($val->qty,2);
            $sub_array[] =  $val->uom_name;
            $sub_array[] =  strlen($val->remark)<=20 ? $val->remark : substr($val->remark,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->remark.'"></i>';
            $data1[] = $sub_array;
            $sr_no++;
        }
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }
    public function update_consume(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $issued_return_ids   =   $this->input->post('issued_return');
            $inventory_id   =   $this->input->post('inventory_id');
            if(isset($issued_return_ids) && !empty($issued_return_ids)){
                //====get  issue return or not==//
                $this->db->where_in('id',$issued_return_ids);
                $this->db->where_in('inventory_id',$inventory_id);
                $preqry = $this->db->select('issued_return_for')->get('issued_mgt');
                if($preqry->num_rows()){
                    $issue_retun_for_ids = array();
                    foreach($preqry->result() as $row){
                        $issue_retun_for_ids[$row->issued_return_for]=$row->issued_return_for;
                    }
                }
                $this->db->select('id');
                $this->db->where_in('inventory_id',$inventory_id);
                $this->db->where_in('issued_return_for',$issue_retun_for_ids);
                $qry = $this->db->get('issued_return_mgt');
                //====get  issue return or not==//
                if($qry->num_rows()){
                    $response['status']     =   '0';
                    $response['message']    =   'Sorry issued Already Returned';
                }else{
                    $upd = array();
                    $upd['consume']  =   1;

                    $this->db->where_in('id',$issued_return_ids);
                    $this->db->where_in('inventory_id',$inventory_id);
                    $qry_return = $this->db->update('issued_mgt',$upd);
                    if($qry_return){
                        $response['status']     =   '1';
                        $response['message']    =   'Issued Consumed  Successfully';
                    }
                    else{
                        $response['status']     =   '0';
                        $response['message']    =   'please try again letter';
                    }
                }

            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Issued  Not Found';
            }
        }
        echo json_encode($response); die;
    }
    public function get_stock_report_data()
    {
        $get_filter_id = array();
        if($_POST['stock_qty'] && $_POST['stock_qty']!=""){
            $get_filter_id  =   $this->get_pre_inventory_stock_report($_POST['stock_qty']);
        }
        $stock_form_date    =   $this->input->post('stock_form_date',true);
        $stock_to_date      =   $this->input->post('stock_to_date',true);
        if(isset($stock_form_date) && $stock_form_date!="" && isset($stock_to_date) && $stock_to_date!=""){
            $stock_form_date_arr    =   explode('/',$stock_form_date);
            $stock_to_date_arr      =   explode('/',$stock_to_date);
            $new_from_date          =   $stock_form_date_arr[2].'-'.$stock_form_date_arr[1].'-'.$stock_form_date_arr[0].' 00:00:00';
            $new_to_date            =   $stock_to_date_arr[2].'-'.$stock_to_date_arr[1].'-'.$stock_to_date_arr[0].' 23:59:59';
        }
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id,$get_filter_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id,$get_filter_id);
        $invarrs        =   array();
        $grn_price_arr  =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]             =   $resp_val->id;
                $grn_price_arr['os'][$resp_val->id] =   $resp_val->stock_price;
            }
        }

        $data1      =   [];

        if(isset($invarrs) && !empty($invarrs)){
            if(isset($new_from_date) && isset($new_to_date)){
                //====Pre Cureent Stock===//
                //====TOTAL RFQ===//
                $pre_rfq_qty                        =   array();
                $pre_close_rfq_id_arr               =   array();
                $pre_rfq_ids_against_inventory_id   =   array();
                $pre_rfq_tot_price_id               =   array();
                $pre_rfq_tot_price_inv_id           =   array();

                $respose_pre_close =   $this->get_pre_close_rfq_id_pre_rfq_ids_against_inventory_pre_rfq_qty($invarrs, $new_from_date, $new_to_date);
                $pre_close_rfq_id_arr               =   $respose_pre_close['pre_close_rfq_id_arr'];
                $pre_rfq_ids_against_inventory_id   =   $respose_pre_close['pre_rfq_ids_against_inventory_id'];
                $pre_rfq_qty                        =   $respose_pre_close['pre_rfq_qty'];
                //pr($pre_rfq_qty); die;
                //===For order RFQ===//
                $respose_pre_rfq_tot_price  =   $this->get_pre_rfq_tot_price_pre_rfq_tot_price_inv($invarrs, $new_from_date);
                $pre_rfq_tot_price_id       =   $respose_pre_rfq_tot_price['pre_rfq_tot_price_id'];
                $pre_rfq_tot_price_inv_id   =   $respose_pre_rfq_tot_price['pre_rfq_tot_price_inv_id'];
                //===For Order RFQ===//
                //====TOTAL RFQ===//
                //===Closed RFQ Qty=====//
                $pre_close_price_ids    =   array();
                $pre_closed_order       =   array();
                $pre_final_close_order  =   array();
                $pre_get_inv_ids_price  =   array();
                if(isset($pre_close_rfq_id_arr) && !empty($pre_close_rfq_id_arr)){
                    $respose_pre_close_price_ids  =     $this->get_pre_close_price_ids_pre_get_inv_ids_price($pre_close_rfq_id_arr, $new_from_date);
                    $pre_close_price_ids          =     $respose_pre_close_price_ids['pre_close_price_ids'];
                    $pre_get_inv_ids_price        =     $respose_pre_close_price_ids['pre_get_inv_ids_price'];
                }
                if(isset($pre_close_price_ids) && !empty($pre_close_price_ids)){
                    $respose_pre_closed_order   =   $this->get_pre_closed_order_pre_final_close_order($pre_close_price_ids,$pre_get_inv_ids_price, $new_from_date);
                    $pre_closed_order           =   $respose_pre_closed_order['pre_closed_order'];
                    $pre_final_close_order      =   $respose_pre_closed_order['pre_final_close_order'];
                }
                //===Closed RFQ Qty=====//
                //===Place Order====//
                $pre_order_price_ids            =   array();
                $pre_place_order_inv_ids_price  =   array();
                $pre_place_order                =   array();
                $pre_final_place_order          =   array();
                //pr($pre_rfq_tot_price_id); die;
                if(isset($pre_rfq_tot_price_id) && !empty($pre_rfq_tot_price_id)){
                    $respose_pre_order_price_ids    =   $this->get_pre_order_price_ids_pre_place_order_inv_ids_price($pre_rfq_tot_price_id,$pre_rfq_tot_price_inv_id, $new_from_date);
                    $pre_order_price_ids            =   $respose_pre_order_price_ids['pre_order_price_ids'];
                    $pre_place_order_inv_ids_price  =   $respose_pre_order_price_ids['pre_place_order_inv_ids_price'];

                }
                if(isset($pre_order_price_ids) && !empty($pre_order_price_ids)){
                    $respose_pre_place_order    =   $this->get_pre_place_order_pre_final_place_order($pre_order_price_ids,$pre_place_order_inv_ids_price, $new_from_date);
                    $pre_place_order            =   $respose_pre_place_order['pre_place_order'];
                    $pre_final_place_order      =   $respose_pre_place_order['pre_final_place_order'];
                }
                //pr($final_place_order); die;
                //===Place Order====//
                //====Wpo price===//
                $pre_wpo_price = array();
                $respose_pre_wpo_price  =   $this->get_pre_wpo_price($invarrs);
                $pre_wpo_price          =   $respose_pre_wpo_price;

                $this->db->select('manual_po_number, inventory_id, product_price')
                ->from('tbl_manual_po_order')
                ->where_in('inventory_id', $invarrs)
                ->where('product_price !=', '');
                $pre_qry_rfq_price = $this->db->get();
                if($pre_qry_rfq_price->num_rows()){
                    foreach($pre_qry_rfq_price->result() as $pre_rp_row){
                        $pre_wpo_price[$pre_rp_row->manual_po_number][$pre_rp_row->inventory_id] = $pre_rp_row->product_price;
                    }
                }
                //pr($pre_wpo_price); die;
                //====wpo price===//
                //===GRN====//

                $pre_new_grn_wpo_arr            =   array();
                $respose_pre_new_grn_wpo_arr    =   $this->get_pre_new_grn_wpo_arr($invarrs,$new_from_date, $new_to_date);
                $pre_new_grn_wpo_arr            =   $respose_pre_new_grn_wpo_arr;
                //===GRN WPO====//
                $pre_grn_wpo_arr        =   array();
                $pre_grn_wpo_price_arr  =   array();
                $respose_pre_grn_wpo_pre_grn_wpo_price    =   $this->get_pre_grn_wpo_pre_grn_wpo_price($invarrs,$new_from_date, $new_to_date);
                $pre_grn_wpo_arr        =   $respose_pre_grn_wpo_pre_grn_wpo_price['pre_grn_wpo_arr'];
                $pre_grn_wpo_price_arr  =   $respose_pre_grn_wpo_pre_grn_wpo_price['pre_grn_wpo_price_arr'];
                $pre_grn_price_arr      =   $respose_pre_grn_wpo_pre_grn_wpo_price['pre_grn_price_arr'];

                //pr($pre_grn_wpo_arr); die;
                //===GRN WPO====//
                //===GRN WOPO====//

                $pre_grn_wopo_arr           =   array();
                $pre_grn_wopo_price_arr     =   array();

                $this->db->select('id, grn_qty, inventory_id, rate')
                ->from('grn_mgt')
                ->where_in('inventory_id', $invarrs)
                ->where([
                    'grn_type' => '2',
                    'is_deleted' => '0'
                ]);
                if (!empty($new_from_date) && !empty($new_to_date)) {
                    $this->db->where('last_updated_date <', $new_from_date);
                }
                $pre_qry_grn_wop = $this->db->get();
                if($pre_qry_grn_wop->num_rows()){
                    foreach($pre_qry_grn_wop->result() as $pre_grn_wop_res){
                        if(isset($pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]+$pre_grn_wop_res->grn_qty;
                        }
                        else{
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wop_res->grn_qty;
                        }
                        if(isset($pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]+(($pre_grn_wop_res->grn_qty)*(round($pre_grn_wop_res->rate,2)));
                        }
                        else{
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   ($pre_grn_wop_res->grn_qty)*(round($pre_grn_wop_res->rate,2));
                        }
                        $pre_grn_price_arr[$pre_grn_wop_res->id] =   round($pre_grn_wop_res->rate,2);
                    }
                }
                //===GRN WOPO====//
                //===Stock GRN===//
                $pre_grn_stock_arr          =   array();
                $pre_grn_stock_price_arr    =   array();
                $respose_pre_grn_stock_pre_grn_stock_price    =   $this->get_pre_grn_stock_pre_grn_stock_price($invarrs, $pre_grn_price_arr, $new_from_date, $new_to_date);
                $pre_grn_stock_arr          =   $respose_pre_grn_stock_pre_grn_stock_price['pre_grn_stock_arr'];
                $pre_grn_stock_price_arr    =   $respose_pre_grn_stock_pre_grn_stock_price['pre_grn_stock_price_arr'];

                //===Stock GRN===//
                //===Issued===//
                $pre_issued_arr = array();
                $pre_issued_price_arr = array();
                $respose_pre_issued_pre_issued_price    =   $this->get_pre_issued_pre_issued_price($invarrs, $pre_grn_price_arr, $new_from_date);
                $pre_issued_arr         =   $respose_pre_issued_pre_issued_price['pre_issued_arr'];
                $pre_issued_price_arr   =   $respose_pre_issued_pre_issued_price['pre_issued_price_arr'];

                //===Issued===//
                //====Issued Return===//
                $pre_issued_return_arr = array();
                $pre_issued_return_price_arr = array();
                $respose_pre_issued_return_pre_issued_return_price    =   $this->get_pre_issued_return_pre_issued_return_price($invarrs, $pre_grn_price_arr, $new_from_date);
                $pre_issued_return_arr          =   $respose_pre_issued_return_pre_issued_return_price['pre_issued_return_arr'];
                $pre_issued_return_price_arr    =   $respose_pre_issued_return_pre_issued_return_price['pre_issued_return_price_arr'];

                // pr($issued_return_arr);
                // pr($issued_return_price_arr);die;
                //====Issued Return===//
                //===Stock Return=====//
                $pre_stock_return_arr = array();
                $pre_stock_return_price_arr = array();
                $respose_pre_stock_return_pre_stock_return_price    =   $this->get_pre_stock_return_pre_stock_return_price($invarrs, $pre_grn_price_arr, $new_from_date);
                $pre_stock_return_arr       =   $respose_pre_stock_return_pre_stock_return_price['pre_stock_return_arr'];
                $pre_stock_return_price_arr =   $respose_pre_stock_return_pre_stock_return_price['pre_stock_return_price_arr'];

                //===Stock Return=====//
                //====Pre Cureent Stock===//
            }
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $respose_close_rfq_id_rfq_ids_against_inventory_rfq_qty    =   $this->get_close_rfq_id_rfq_ids_against_inventory_rfq_qty($invarrs, $new_from_date, $new_to_date);
            $close_rfq_id_arr               =   $respose_close_rfq_id_rfq_ids_against_inventory_rfq_qty['close_rfq_id_arr'];
            $rfq_ids_against_inventory_id   =   $respose_close_rfq_id_rfq_ids_against_inventory_rfq_qty['rfq_ids_against_inventory_id'];
            $rfq_qty                        =   $respose_close_rfq_id_rfq_ids_against_inventory_rfq_qty['rfq_qty'];

            //===For order RFQ===//
            $respose_rfq_tot_price_rfq_tot_price_inv    =   $this->get_rfq_tot_price_rfq_tot_price_inv($invarrs, $new_from_date, $new_to_date);
            $rfq_tot_price_id       =   $respose_rfq_tot_price_rfq_tot_price_inv['rfq_tot_price_id'];
            $rfq_tot_price_inv_id   =   $respose_rfq_tot_price_rfq_tot_price_inv['rfq_tot_price_inv_id'];

            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $respose_close_price_get_inv_ids_price    =   $this->get_close_price_get_inv_ids_price($close_rfq_id_arr, $new_from_date, $new_to_date);
                $close_price_ids    =   $respose_close_price_get_inv_ids_price['close_price_ids'];
                $get_inv_ids_price    =   $respose_close_price_get_inv_ids_price['get_inv_ids_price'];

            }
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $response_closed_order_final_close_order        =   $this->get_closed_order_final_close_order($close_price_ids,$get_inv_ids_price);
                $closed_order                                   =    $response_closed_order_final_close_order['closed_order'];
                $final_close_order                              =    $response_closed_order_final_close_order['final_close_order'];

            }
            //===Closed RFQ Qty=====//

            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $response_order_price_place_order_inv_ids_price        =   $this->get_order_price_place_order_inv_ids_price($rfq_tot_price_id, $rfq_tot_price_inv_id, $new_from_date, $new_to_date);
                $order_price_ids                =   $response_order_price_place_order_inv_ids_price['order_price_ids'];
                $place_order_inv_ids_price      =   $response_order_price_place_order_inv_ids_price['place_order_inv_ids_price'];

            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $response_place_order_final_place_order        =   $this->get_place_order_final_place_order_stock($order_price_ids, $place_order_inv_ids_price, $new_from_date, $new_to_date);
                $place_order        =   $response_place_order_final_place_order['place_order'];
                $final_place_order  =   $response_place_order_final_place_order['final_place_order'];
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);

            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number][$rp_row->inventory_id] = $rp_row->order_price;
                }
            }
            $qry_rfq_price = $this->db->get_where('tbl_manual_po_order',array('product_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->manual_po_number][$rp_row->inventory_id] = round($rp_row->product_price,2);
                }
            }
            //====wpo price===//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $response_new_grn_wpo        =   $this->get_new_grn_wpo($invarrs, $new_from_date, $new_to_date);
            $new_grn_wpo_arr    =   $response_new_grn_wpo;
            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();


            $this->db->select('id, grn_qty, inventory_id, po_number, grn_buyer_rate')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->where('is_deleted', '0');

            if (!empty($new_from_date) && !empty($new_to_date)) {
                $this->db->where('last_updated_date >=', $new_from_date);
                $this->db->where('last_updated_date <=', $new_to_date);
            }

            $qry_grn_wp = $this->db->get();
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + round($grn_wp_res->grn_qty*round($per_price,2),2);
                    }
                    else{
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   round($grn_wp_res->grn_qty*round($per_price,2),2);
                    }
                    $per_price_new = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    if($grn_wp_res->grn_buyer_rate>0){
                        $per_price_new = $grn_wp_res->grn_buyer_rate;
                    }
                    //$grn_price_arr[$grn_wp_res->id] =   $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    $grn_price_arr[$grn_wp_res->id] =   $per_price_new;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//

            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*(round($grn_wop_res->rate,2)));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*(round($grn_wop_res->rate,2));
                    }
                    $grn_price_arr[$grn_wop_res->id] =   round($grn_wop_res->rate,2);
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $grn_stock_price_arr =   array();
            $response_new_grn_wpo        =   $this->get_grn_stock_grn_stock_price($invarrs, $new_from_date, $new_to_date, $grn_price_arr);
            $grn_stock_arr          =   $response_new_grn_wpo['grn_stock_arr'];
            $grn_stock_price_arr    =   $response_new_grn_wpo['grn_stock_price_arr'];

            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr         = array();
            $issued_price_arr   = array();
            $response_issued_issued_price        =   $this->get_issued_issued_price($invarrs, $new_from_date, $new_to_date, $grn_price_arr);
            $issued_arr         =  $response_issued_issued_price['issued_arr'];
            $issued_price_arr   =  $response_issued_issued_price['issued_price_arr'];

            //===Issued===//
            //====Issued Return===//
            $issued_return_arr = array();
            $issued_return_price_arr = array();
            $response_issued_return_issued_return_price        =   $this->get_issued_return_issued_return_price($invarrs, $new_from_date, $new_to_date, $grn_price_arr);
            $issued_return_arr         =  $response_issued_return_issued_return_price['issued_return_arr'];
            $issued_return_price_arr   =  $response_issued_return_issued_return_price['issued_return_price_arr'];

            // pr($issued_return_arr);
            // pr($issued_return_price_arr);die;
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $stock_return_price_arr = array();

            $response_stock_return_stock_return_price        =   $this->get_stock_return_stock_return_price($invarrs, $new_from_date, $new_to_date, $grn_price_arr);
            $stock_return_arr         =  $response_stock_return_stock_return_price['stock_return_arr'];
            $stock_return_price_arr   =  $response_stock_return_stock_return_price['stock_return_price_arr'];
            //pr($mystok_price); //die;
            //pr($stock_return_price_arr); die;
            //===Stock Return=====//
        }
        // pr($grn_stock_arr);die;
        $data1 = array();
        // foreach ($result as $key => $val) {
        //     //===Indent Qty==//
        //         //$total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
        //     //===Indent Qty===//
        //     //====RFQ QTY ====//
        //     $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
        //     if(isset($final_close_order[$val->id])){
        //         $total_RFQ = $total_RFQ+$final_close_order[$val->id];
        //     }
        //     //===RFQ QTY======//
        //     //====Place Order===//
        //     $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
        //     //====Place Order===//
        //     //===GRN====//
        //     $grn_qty = 0;
        //     if(isset($grn_wpo_arr[$val->id])){
        //         $grn_qty = $grn_wpo_arr[$val->id];
        //     }
        //     $grn_qty_price = 0;
        //     if(isset($grn_wpo_price_arr[$val->id])){
        //         $grn_qty_price = round($grn_wpo_price_arr[$val->id],2);
        //     }
        //     $grn_qty_wop = 0;
        //     if(isset($grn_wopo_arr[$val->id])){
        //         $grn_qty_wop = $grn_wopo_arr[$val->id];
        //     }
        //     $grn_qty_wop_price = 0;
        //     if(isset($grn_wopo_price_arr[$val->id])){
        //         $grn_qty_wop_price = round($grn_wopo_price_arr[$val->id],2);
        //     }
        //     $grn_qty_stok = 0;
        //     if(isset($grn_stock_arr[$val->id])){
        //         $grn_qty_stok = $grn_stock_arr[$val->id];
        //     }

        //     $grn_qty_stok_price = 0;
        //     if(isset($grn_stock_price_arr[$val->id])){
        //         $grn_qty_stok_price = round($grn_stock_price_arr[$val->id],2);
        //     }

        //     //====GRN====//
        //     //===Issued=====//
        //     $issued_qty = 0;
        //     if(isset($issued_arr[$val->id])){
        //         $issued_qty = $issued_arr[$val->id];
        //     }
        //     $issued_qty_price = 0;
        //     if(isset($issued_price_arr[$val->id])){
        //         $issued_qty_price = round($issued_price_arr[$val->id],2);
        //     }
        //     //===Issued=====//
        //     //===Isseued Return==//
        //     $issued_return_qty = 0;
        //     if(isset($issued_arr[$val->id])){
        //         $issued_return_qty = $issued_return_arr[$val->id];
        //     }
        //     $issued_return_qty_price = 0;
        //     if(isset($issued_return_price_arr[$val->id])){
        //         $issued_return_qty_price = round($issued_return_price_arr[$val->id],2);
        //     }
        //     //===Issued Return===//
        //     //===Stock Return===//
        //     $stock_return_qty = 0;
        //     if(isset($stock_return_arr[$val->id])){
        //         $stock_return_qty = $stock_return_arr[$val->id];
        //     }
        //     $stock_return_qty_price = 0;
        //     if(isset($stock_return_price_arr[$val->id])){
        //         $stock_return_qty_price = round($stock_return_price_arr[$val->id],2);
        //     }
        //     //===Stock Return====//
        //     $sub_array = array();
        //     //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
        //     $opening_stock_price = $val->opening_stock*round($val->stock_price,2);
        //     $mystock            =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
        //     $mystock_price      =   (round($opening_stock_price,2)+round($grn_qty_price,2)+round($grn_qty_wop_price,2)+round($grn_qty_stok_price,2)+round($issued_return_qty_price,2))-(round($issued_qty_price,2)+round($stock_return_qty_price,2));
        //     if(isset($new_from_date) && isset($new_to_date)){
        //         //===pre GRN====//
        //         $pre_grn_qty = 0;
        //         if(isset($pre_grn_wpo_arr[$val->id])){
        //             $pre_grn_qty = $pre_grn_wpo_arr[$val->id];
        //         }
        //         $pre_grn_qty_price = 0;
        //         if(isset($pre_grn_wpo_price_arr[$val->id])){
        //             $pre_grn_qty_price = round($pre_grn_wpo_price_arr[$val->id],2);
        //         }
        //         $pre_grn_qty_wop = 0;
        //         if(isset($pre_grn_wopo_arr[$val->id])){
        //             $pre_grn_qty_wop = $pre_grn_wopo_arr[$val->id];
        //         }
        //         $pre_grn_qty_wop_price = 0;
        //         if(isset($pre_grn_wopo_price_arr[$val->id])){
        //             $pre_grn_qty_wop_price = round($pre_grn_wopo_price_arr[$val->id],2);
        //         }
        //         $pre_grn_qty_stok = 0;
        //         if(isset($pre_grn_stock_arr[$val->id])){
        //             $pre_grn_qty_stok = $pre_grn_stock_arr[$val->id];
        //         }

        //         $pre_grn_qty_stok_price = 0;
        //         if(isset($pre_grn_stock_price_arr[$val->id])){
        //             $pre_grn_qty_stok_price = round($pre_grn_stock_price_arr[$val->id],2);
        //         }

        //         //====GRN====//
        //         //===Issued=====//
        //         $pre_issued_qty = 0;
        //         if(isset($pre_issued_arr[$val->id])){
        //             $pre_issued_qty = $pre_issued_arr[$val->id];
        //         }
        //         $pre_issued_qty_price = 0;
        //         if(isset($pre_issued_price_arr[$val->id])){
        //             $pre_issued_qty_price = round($pre_issued_price_arr[$val->id],2);
        //         }
        //         //===Issued=====//
        //         //===Isseued Return==//
        //         $pre_issued_return_qty = 0;
        //         if(isset($pre_issued_arr[$val->id])){
        //             $pre_issued_return_qty = $pre_issued_return_arr[$val->id];
        //         }
        //         $pre_issued_return_qty_price = 0;
        //         if(isset($pre_issued_return_price_arr[$val->id])){
        //             $pre_issued_return_qty_price = round($pre_issued_return_price_arr[$val->id],2);
        //         }
        //         //===Issued Return===//
        //         //===Stock Return===//
        //         $pre_stock_return_qty = 0;
        //         if(isset($pre_stock_return_arr[$val->id])){
        //             $pre_stock_return_qty = $pre_stock_return_arr[$val->id];
        //         }
        //         $pre_stock_return_qty_price = 0;
        //         if(isset($pre_stock_return_price_arr[$val->id])){
        //             $pre_stock_return_qty_price = round($pre_stock_return_price_arr[$val->id],2);
        //         }
        //         //===Stock Return====//
        //         $mystock    =   ($mystock+$pre_grn_qty+$pre_grn_qty_wop+$pre_grn_qty_stok+$pre_issued_return_qty)-($pre_issued_qty+$pre_stock_return_qty);
        //         $mystock_price      =   (round($mystock_price,2)+round($pre_grn_qty_price,2)+round($pre_grn_qty_wop_price,2)+round($pre_grn_qty_stok_price,2)+round($pre_issued_return_qty_price,2))-(round($pre_issued_qty_price,2)+round($pre_stock_return_qty_price,2));
        //     }

        //     $sub_array      =   array();
        //     $sub_array[]    =   $key+1;
        //     $sub_array[]    =   $val->prod_name;
        //     $sub_array[]    =   strlen($val->buyer_product_name)<=20 ? $val->buyer_product_name : substr($val->buyer_product_name,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->buyer_product_name.'"></i>';
        //     $sub_array[]    =   strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
        //     $sub_array[]    =   strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>'.$is_del_invs;
        //     $sub_array[]    =   strlen($val->inventory_grouping) <=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
        //     $sub_array[]    =   $val->uom_name;
        //     //($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
        //     //$sub_array[]    =   round($mystock,2).'-'.$val->opening_stock.'-'.$grn_qty.'-'.$grn_qty_wop.'-'.$grn_qty_stok.'-'.$issued_return_qty.'='.$issued_qty.'='.$stock_return_qty;
        //     $sub_array[]    =   round($mystock,2);
        //     // $sub_array[]   =   formatIndianRupees($mystock_price);
        //     $mystock_price  =   round($mystock_price,2);
        //     if($mystock_price>=1){
        //         $formatted_price = formatIndianRupees($mystock_price);
        //     }
        //     else{
        //         $formatted_price = $mystock_price >= '.01' ? $mystock_price : '0.00';
        //     }
        //     if (strpos($formatted_price, '.') === false) {
        //         $formatted_price .= '.00';
        //     }
        //     //(round($opening_stock_price,2)+round($grn_qty_price,2)+round($grn_qty_wop_price,2)+round($grn_qty_stok_price,2)+round($issued_return_qty_price,2))-(round($issued_qty_price,2)+round($stock_return_qty_price,2))
        //     //$sub_array[]=$formatted_price.'-'.round($opening_stock_price,2).'-'.round($grn_qty_price,2).'-'.round($grn_qty_wop_price,2).'-'.round($grn_qty_stok_price,2).'-'.round($issued_return_qty_price,2).'='.round($issued_qty_price,2).'='.round($stock_return_qty_price,2);
        //     $sub_array[]    =   $formatted_price;
        //     if($mystock>0 || $issued_qty>0){
        //         $orgnal_issued_qty = $issued_qty-$issued_return_qty;
        //         $sub_array[] = round($orgnal_issued_qty,2);
        //     }
        //     else{
        //         $sub_array[] = '0';
        //     }
        //     if($mystock>0 || $issued_qty>0){
        //         $orgnal_issued_qty_price = round($issued_qty_price,2)-round($issued_return_qty_price,2);
        //         // $sub_array[] = formatIndianRupees($orgnal_issued_qty_price);
        //         if($orgnal_issued_qty_price>=1){
        //             $formatted_price = formatIndianRupees($orgnal_issued_qty_price);
        //         }
        //         else{
        //             $formatted_price = $orgnal_issued_qty_price >= '.01' ? $orgnal_issued_qty_price : '0.00';
        //         }
        //         if (strpos($formatted_price, '.') === false) {
        //             $formatted_price .= '.00';
        //         }
        //         $sub_array[]=$formatted_price;
        //     }
        //     else{
        //         $sub_array[] = '0.00';
        //     }
        //     if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok>0){
        //         $totgrn         =   $grn_qty+$grn_qty_wop + $grn_qty_stok;
        //         $sub_array[]    =   round($totgrn,2);
        //     }
        //     else{
        //         $sub_array[] = 0;
        //     }
        //     if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok_price>0){
        //         // $sub_array[] = $grn_qty_price;
        //         $grn_qty_price_final    =   round($grn_qty_price,2) + round($grn_qty_wop_price,2) + round($grn_qty_stok_price,2);
        //         //  $sub_array[]           =   formatIndianRupees($grn_qty_price_final);
        //         if($grn_qty_price_final>'1'){
        //             $formatted_price = formatIndianRupees($grn_qty_price_final);
        //         }
        //         else{
        //             $formatted_price = $grn_qty_price_final >= '.01' ? $grn_qty_price_final : '0.00';
        //         }
        //         if (strpos($formatted_price, '.') === false) {
        //             $formatted_price .= '.00';
        //         }
        //         $sub_array[]=$formatted_price;
        //     }
        //     else{
        //         $sub_array[] = '0.00';
        //     }
        //     $data1[] = $sub_array;
        // }

        foreach ($result as $key => $val) {
            $id = $val->id;
            $total_RFQ = ($rfq_qty[$id] ?? 0) + ($final_close_order[$id] ?? 0);
            $totl_order = $final_place_order[$id] ?? 0;

            $grn_qty = $grn_wpo_arr[$id] ?? 0;
            $grn_qty_price = round($grn_wpo_price_arr[$id] ?? 0, 2);
            $grn_qty_wop = $grn_wopo_arr[$id] ?? 0;
            $grn_qty_wop_price = round($grn_wopo_price_arr[$id] ?? 0, 2);
            $grn_qty_stok = $grn_stock_arr[$id] ?? 0;
            $grn_qty_stok_price = round($grn_stock_price_arr[$id] ?? 0, 2);

            $issued_qty = $issued_arr[$id] ?? 0;
            $issued_qty_price = round($issued_price_arr[$id] ?? 0, 2);
            $issued_return_qty = $issued_return_arr[$id] ?? 0;
            $issued_return_qty_price = round($issued_return_price_arr[$id] ?? 0, 2);

            $stock_return_qty = $stock_return_arr[$id] ?? 0;
            $stock_return_qty_price = round($stock_return_price_arr[$id] ?? 0, 2);

            $opening_stock_price = $val->opening_stock * round($val->stock_price, 2);
            $mystock = ($val->opening_stock + $grn_qty + $grn_qty_wop + $grn_qty_stok + $issued_return_qty) - ($issued_qty + $stock_return_qty);
            $mystock_price = round($opening_stock_price + $grn_qty_price + $grn_qty_wop_price + $grn_qty_stok_price + $issued_return_qty_price - $issued_qty_price - $stock_return_qty_price, 2);

            if (isset($new_from_date, $new_to_date)) {
                $pre_grn_qty = $pre_grn_wpo_arr[$id] ?? 0;
                $pre_grn_qty_price = round($pre_grn_wpo_price_arr[$id] ?? 0, 2);
                $pre_grn_qty_wop = $pre_grn_wopo_arr[$id] ?? 0;
                $pre_grn_qty_wop_price = round($pre_grn_wopo_price_arr[$id] ?? 0, 2);
                $pre_grn_qty_stok = $pre_grn_stock_arr[$id] ?? 0;
                $pre_grn_qty_stok_price = round($pre_grn_stock_price_arr[$id] ?? 0, 2);

                $pre_issued_qty = $pre_issued_arr[$id] ?? 0;
                $pre_issued_qty_price = round($pre_issued_price_arr[$id] ?? 0, 2);
                $pre_issued_return_qty = $pre_issued_return_arr[$id] ?? 0;
                $pre_issued_return_qty_price = round($pre_issued_return_price_arr[$id] ?? 0, 2);

                $pre_stock_return_qty = $pre_stock_return_arr[$id] ?? 0;
                $pre_stock_return_qty_price = round($pre_stock_return_price_arr[$id] ?? 0, 2);

                $mystock += $pre_grn_qty + $pre_grn_qty_wop + $pre_grn_qty_stok + $pre_issued_return_qty - $pre_issued_qty - $pre_stock_return_qty;
                $mystock_price += round($pre_grn_qty_price + $pre_grn_qty_wop_price + $pre_grn_qty_stok_price + $pre_issued_return_qty_price - $pre_issued_qty_price - $pre_stock_return_qty_price, 2);
            }

            $formatted_price = $mystock_price >= 1 ? formatIndianRupees($mystock_price) : ($mystock_price >= 0.01 ? $mystock_price : '0.00');
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }

            $orgnal_issued_qty = $issued_qty - $issued_return_qty;
            $orgnal_issued_qty_price = round($issued_qty_price - $issued_return_qty_price, 2);

            $formatted_issued_price = $orgnal_issued_qty_price >= 1 ? formatIndianRupees($orgnal_issued_qty_price) : ($orgnal_issued_qty_price >= 0.01 ? $orgnal_issued_qty_price : '0.00');
            if (strpos($formatted_issued_price, '.') === false) {
                $formatted_issued_price .= '.00';
            }

            $totgrn = $grn_qty + $grn_qty_wop + $grn_qty_stok;
            $grn_qty_price_final = round($grn_qty_price + $grn_qty_wop_price + $grn_qty_stok_price, 2);

            $formatted_grn_price = $grn_qty_price_final > 1 ? formatIndianRupees($grn_qty_price_final) : ($grn_qty_price_final >= 0.01 ? $grn_qty_price_final : '0.00');
            if (strpos($formatted_grn_price, '.') === false) {
                $formatted_grn_price .= '.00';
            }

            $data1[] = [
                $key + 1,
                $val->prod_name,
                (strlen($val->buyer_product_name) <= 20) ? $val->buyer_product_name : substr($val->buyer_product_name, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->buyer_product_name . '"></i>',
                (strlen($val->specification) <= 20) ? $val->specification : substr($val->specification, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->specification . '"></i>',
                (strlen($val->size) <= 20) ? $val->size : substr($val->size, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->size . '"></i>',
                (strlen($val->inventory_grouping) <= 20) ? $val->inventory_grouping : substr($val->inventory_grouping, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->inventory_grouping . '"></i>',
                $val->uom_name,
                round($mystock, 2),
                $formatted_price,
                $orgnal_issued_qty > 0 ? round($orgnal_issued_qty, 2) : '0',
                $formatted_issued_price,
                $totgrn > 0 ? round($totgrn, 2) : 0,
                $formatted_grn_price
            ];
        }
        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  count($data1),
            "recordsFiltered"   =>  $total_record,
            "data" => $data1
        );

        echo json_encode($output);
    }

    public function get_pre_close_rfq_id_pre_rfq_ids_against_inventory_pre_rfq_qty($invarrs, $new_from_date = null, $new_to_date = null) {
        $this->db->group_by('variant_grp_id');
        $this->db->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_at <', $new_from_date);
        }

        $query = $this->db->select('MAX(id) as id, MAX(rfq_id) as rfq_id, MAX(inventory_id) as inventory_id, MAX(quantity) as quantity, MAX(buyer_rfq_status) as buyer_rfq_status')
            ->get_where('tbl_rfq', array('record_type' => '2', 'inv_status' => '1'));

        $pre_close_rfq_id_arr = [];
        $pre_rfq_ids_against_inventory_id = [];
        $pre_rfq_qty = [];

        if ($query->num_rows()) {
            foreach ($query->result() as $row) {
                if ($row->buyer_rfq_status == 8 || $row->buyer_rfq_status == 10) {
                    $pre_close_rfq_id_arr[$row->id] = $row->id;
                    $pre_rfq_ids_against_inventory_id[$row->id] = $row->inventory_id;
                } else {
                    $pre_rfq_qty[$row->inventory_id] = isset($pre_rfq_qty[$row->inventory_id])
                        ? ($pre_rfq_qty[$row->inventory_id] + $row->quantity)
                        : $row->quantity;
                }
            }
        }

        return [
            'pre_close_rfq_id_arr' => $pre_close_rfq_id_arr,
            'pre_rfq_ids_against_inventory_id' => $pre_rfq_ids_against_inventory_id,
            'pre_rfq_qty' => $pre_rfq_qty,
        ];
    }

    public function get_pre_rfq_tot_price_pre_rfq_tot_price_inv($invarrs, $new_from_date = null) {
        $this->db->select('id, rfq_id, inventory_id, quantity, buyer_rfq_status')
            ->from('tbl_rfq')
            ->where(['record_type' => '2', 'inv_status' => '1'])
            ->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date)) {
            $this->db->where('updated_at <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_rfq_tot_price_id = [];
        $pre_rfq_tot_price_inv_id = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_rfq_tot_price_id[$row->id] = $row->id;
                $pre_rfq_tot_price_inv_id[$row->id] = $row->inventory_id;
            }
        }

        return [
            'pre_rfq_tot_price_id' => $pre_rfq_tot_price_id,
            'pre_rfq_tot_price_inv_id' => $pre_rfq_tot_price_inv_id,
        ];
    }

    public function get_pre_close_price_ids_pre_get_inv_ids_price($pre_close_rfq_id_arr, $new_from_date = null) {
        if (empty($pre_close_rfq_id_arr)) {
            return ['pre_close_price_ids' => [], 'pre_get_inv_ids_price' => []];
        }

        $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $pre_close_rfq_id_arr);

        if (!empty($new_from_date)) {
            $this->db->where('updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_close_price_ids = [];
        $pre_get_inv_ids_price = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_close_price_ids[$row->id] = $row->id;
                $pre_get_inv_ids_price[$row->id] = isset($pre_rfq_ids_against_inventory_id[$row->rfq_record_id])
                    ? $pre_rfq_ids_against_inventory_id[$row->rfq_record_id]
                    : '';
            }
        }

        return [
            'pre_close_price_ids' => $pre_close_price_ids,
            'pre_get_inv_ids_price' => $pre_get_inv_ids_price,
        ];
    }

    public function get_pre_closed_order_pre_final_close_order($pre_close_price_ids, $pre_get_inv_ids_price, $new_from_date = null) {
        if (empty($pre_close_price_ids)) {
            return [];
        }

        $this->db->select('price_id, order_quantity')
            ->from('tbl_rfq_order')
            ->where_in('price_id', $pre_close_price_ids);

        if (!empty($new_from_date)) {
            $this->db->where('updated_at <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_closed_order = [];
        $pre_final_close_order = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_closed_order[$row->price_id] = isset($pre_closed_order[$row->price_id])
                    ? $pre_closed_order[$row->price_id] + $row->order_quantity
                    : $row->order_quantity;
            }

            foreach ($pre_closed_order as $price_id => $order_quantity) {
                if (isset($pre_get_inv_ids_price[$price_id])) {
                    $pre_final_close_order[$pre_get_inv_ids_price[$price_id]] = $order_quantity;
                }
            }
        }

        return [
            'pre_closed_order' => $pre_closed_order,
            'pre_final_close_order' => $pre_final_close_order,
        ];
    }

    public function get_pre_order_price_ids_pre_place_order_inv_ids_price($pre_rfq_tot_price_id, $pre_rfq_tot_price_inv_id, $new_from_date = null) {
        if (empty($pre_rfq_tot_price_id)) {
            return [];
        }

        $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $pre_rfq_tot_price_id);

        if (!empty($new_from_date)) {
            $this->db->where('updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_order_price_ids = [];
        $pre_place_order_inv_ids_price = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_order_price_ids[$row->id] = $row->id;
                $pre_place_order_inv_ids_price[$row->id] = isset($pre_rfq_tot_price_inv_id[$row->rfq_record_id])
                    ? $pre_rfq_tot_price_inv_id[$row->rfq_record_id]
                    : '';
            }
        }

        return [
            'pre_order_price_ids' => $pre_order_price_ids,
            'pre_place_order_inv_ids_price' => $pre_place_order_inv_ids_price
        ];
    }

    public function get_pre_place_order_pre_final_place_order($pre_order_price_ids, $pre_place_order_inv_ids_price, $new_from_date = null) {
        if (empty($pre_order_price_ids)) {
            return [];
        }

        $this->db->select('price_id, order_quantity')
            ->from('tbl_rfq_order')
            ->where('order_status', '1')
            ->where_in('price_id', $pre_order_price_ids);

        if (!empty($new_from_date)) {
            $this->db->where('updated_at <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_place_order = [];
        $pre_final_place_order = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_place_order[$row->price_id] = isset($pre_place_order[$row->price_id])
                    ? $pre_place_order[$row->price_id] + $row->order_quantity
                    : $row->order_quantity;
            }

            foreach ($pre_place_order as $price_id => $quantity) {
                if (isset($pre_place_order_inv_ids_price[$price_id])) {
                    $inv_id = $pre_place_order_inv_ids_price[$price_id];
                    $pre_final_place_order[$inv_id] = isset($pre_final_place_order[$inv_id])
                        ? $pre_final_place_order[$inv_id] + $quantity
                        : $quantity;
                }
            }
        }

        return [
            'pre_place_order' => $pre_place_order,
            'pre_final_place_order' => $pre_final_place_order
        ];
    }

    public function get_pre_wpo_price($invarrs) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('po_number, inventory_id, order_price')
            ->from('all_rfq_price_order')
            ->where_in('inventory_id', $invarrs)
            ->where('order_price !=', '');

        $query = $this->db->get();
        $pre_wpo_price = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_wpo_price[$row->po_number][$row->inventory_id] = $row->order_price;
            }
        }

        return $pre_wpo_price;
    }

    public function get_pre_new_grn_wpo_arr($invarrs, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('SUM(grn_qty) AS total_grn_quantity, MAX(inventory_id) AS inventory_id')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->where([
                'inv_status' => 1,
                'is_deleted' => '0'
            ]);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >', $new_from_date);
        }

        $this->db->group_by('inventory_id');
        $query = $this->db->get();

        $pre_new_grn_wpo_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $pre_new_grn_wpo_arr[$row->inventory_id] = $row->total_grn_quantity;
            }
        }

        return $pre_new_grn_wpo_arr;
    }

    public function get_pre_grn_wpo_pre_grn_wpo_price($invarrs, $pre_wpo_price, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs) || empty($pre_wpo_price)) {
            return [];
        }

        $this->db->select('id, grn_qty, inventory_id, po_number')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->where('is_deleted', '0');

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_grn_wpo_arr = [];
        $pre_grn_wpo_price_arr = [];
        $pre_grn_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Sum GRN quantity for each inventory ID
                $pre_grn_wpo_arr[$row->inventory_id] = isset($pre_grn_wpo_arr[$row->inventory_id])
                    ? $pre_grn_wpo_arr[$row->inventory_id] + $row->grn_qty
                    : $row->grn_qty;

                // Calculate total price based on GRN quantity and PO price
                $price = isset($pre_wpo_price[$row->po_number][$row->inventory_id])
                    ? $pre_wpo_price[$row->po_number][$row->inventory_id]
                    : 0;

                $pre_grn_wpo_price_arr[$row->inventory_id] = isset($pre_grn_wpo_price_arr[$row->inventory_id])
                    ? $pre_grn_wpo_price_arr[$row->inventory_id] + ($row->grn_qty * $price)
                    : ($row->grn_qty * $price);

                // Store price for each GRN ID
                $pre_grn_price_arr[$row->id] = $price;
            }
        }

        return [
            'pre_grn_wpo_arr' => $pre_grn_wpo_arr,
            'pre_grn_wpo_price_arr' => $pre_grn_wpo_price_arr,
            'pre_grn_price_arr' => $pre_grn_price_arr
        ];
    }

    public function get_pre_grn_stock_pre_grn_stock_price($invarrs, $pre_grn_price_arr, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs) || empty($pre_grn_price_arr)) {
            return [];
        }

        $this->db->select('id, grn_qty, inventory_id, stock_return_for')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where([
                'grn_type' => '3',
                'is_deleted' => '0'
            ]);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_grn_stock_arr = [];
        $pre_grn_stock_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Sum GRN stock return quantity per inventory ID
                $pre_grn_stock_arr[$row->inventory_id] = isset($pre_grn_stock_arr[$row->inventory_id])
                    ? $pre_grn_stock_arr[$row->inventory_id] + $row->grn_qty
                    : $row->grn_qty;

                // Determine stock return price
                $pre_os_grn_price = 0;
                if ($row->stock_return_for == 0) {
                    $pre_os_grn_price = isset($pre_grn_price_arr['os'][$row->inventory_id])
                        ? $pre_grn_price_arr['os'][$row->inventory_id]
                        : 0;
                } else {
                    $pre_os_grn_price = isset($pre_grn_price_arr[$row->stock_return_for])
                        ? $pre_grn_price_arr[$row->stock_return_for]
                        : 0;
                }

                // Calculate total stock return price
                $pre_grn_stock_price_arr[$row->inventory_id] = isset($pre_grn_stock_price_arr[$row->inventory_id])
                    ? $pre_grn_stock_price_arr[$row->inventory_id] + ($row->grn_qty * round($pre_os_grn_price, 2))
                    : ($row->grn_qty * round($pre_os_grn_price, 2));
            }
        }

        return [
            'pre_grn_stock_arr' => $pre_grn_stock_arr,
            'pre_grn_stock_price_arr' => $pre_grn_stock_price_arr
        ];
    }

    public function get_pre_issued_pre_issued_price($invarrs, $pre_grn_price_arr, $new_from_date = null) {
        if (empty($invarrs) || empty($pre_grn_price_arr)) {
            return [];
        }

        $this->db->select('qty, inventory_id, issued_return_for')
            ->from('issued_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where('is_deleted', '0');

        if (!empty($new_from_date)) {
            $this->db->where('last_updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_issued_arr = [];
        $pre_issued_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Sum issued quantities per inventory ID
                $pre_issued_arr[$row->inventory_id] = isset($pre_issued_arr[$row->inventory_id])
                    ? $pre_issued_arr[$row->inventory_id] + $row->qty
                    : $row->qty;

                // Determine issued return price
                $pre_os_grn_price = 0;
                if ($row->issued_return_for == 0) {
                    $pre_os_grn_price = isset($pre_grn_price_arr['os'][$row->inventory_id])
                        ? $pre_grn_price_arr['os'][$row->inventory_id]
                        : 0;
                } else {
                    $pre_os_grn_price = isset($pre_grn_price_arr[$row->issued_return_for])
                        ? $pre_grn_price_arr[$row->issued_return_for]
                        : 0;
                }

                // Calculate total issued price
                $pre_issued_price_arr[$row->inventory_id] = isset($pre_issued_price_arr[$row->inventory_id])
                    ? $pre_issued_price_arr[$row->inventory_id] + ($row->qty * $pre_os_grn_price)
                    : ($row->qty * $pre_os_grn_price);
            }
        }

        return [
            'pre_issued_arr' => $pre_issued_arr,
            'pre_issued_price_arr' => $pre_issued_price_arr
        ];
    }

    public function get_pre_issued_return_pre_issued_return_price($invarrs, $pre_grn_price_arr, $new_from_date = null) {
        if (empty($invarrs) || empty($pre_grn_price_arr)) {
            return [];
        }

        $this->db->select('qty, inventory_id, issued_return_for')
            ->from('issued_return_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where('is_deleted', '0');

        if (!empty($new_from_date)) {
            $this->db->where('last_updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_issued_return_arr = [];
        $pre_issued_return_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Sum issued return quantities per inventory ID
                $pre_issued_return_arr[$row->inventory_id] = isset($pre_issued_return_arr[$row->inventory_id])
                    ? $pre_issued_return_arr[$row->inventory_id] + $row->qty
                    : $row->qty;

                // Determine issued return price
                $pre_os_grn_price = 0;
                if ($row->issued_return_for == 0) {
                    $pre_os_grn_price = isset($pre_grn_price_arr['os'][$row->inventory_id])
                        ? $pre_grn_price_arr['os'][$row->inventory_id]
                        : 0;
                } else {
                    $pre_os_grn_price = isset($pre_grn_price_arr[$row->issued_return_for])
                        ? $pre_grn_price_arr[$row->issued_return_for]
                        : 0;
                }

                // Calculate total issued return price
                $pre_issued_return_price_arr[$row->inventory_id] = isset($pre_issued_return_price_arr[$row->inventory_id])
                    ? $pre_issued_return_price_arr[$row->inventory_id] + ($row->qty * $pre_os_grn_price)
                    : ($row->qty * $pre_os_grn_price);
            }
        }

        return [
            'pre_issued_return_arr' => $pre_issued_return_arr,
            'pre_issued_return_price_arr' => $pre_issued_return_price_arr
        ];
    }

    public function get_pre_stock_return_pre_stock_return_price($invarrs, $pre_grn_price_arr, $new_from_date = null) {
        if (empty($invarrs) || empty($pre_grn_price_arr)) {
            return [];
        }

        $this->db->select('qty, inventory_id, stock_return_for')
            ->from('tbl_return_stock')
            ->where_in('inventory_id', $invarrs)
            ->where('is_deleted', '0');

        if (!empty($new_from_date)) {
            $this->db->where('last_updated_date <', $new_from_date);
        }

        $query = $this->db->get();

        $pre_stock_return_arr = [];
        $pre_stock_return_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                // Sum return stock quantities per inventory ID
                $pre_stock_return_arr[$row->inventory_id] = isset($pre_stock_return_arr[$row->inventory_id])
                    ? $pre_stock_return_arr[$row->inventory_id] + $row->qty
                    : $row->qty;

                // Determine stock return price
                $pre_os_grn_price = 0;
                if ($row->stock_return_for == 0) {
                    $pre_os_grn_price = isset($pre_grn_price_arr['os'][$row->inventory_id])
                        ? $pre_grn_price_arr['os'][$row->inventory_id]
                        : 0;
                } else {
                    $pre_os_grn_price = isset($pre_grn_price_arr[$row->stock_return_for])
                        ? $pre_grn_price_arr[$row->stock_return_for]
                        : 0;
                }

                // Calculate total return stock price
                $pre_stock_return_price_arr[$row->inventory_id] = isset($pre_stock_return_price_arr[$row->inventory_id])
                    ? $pre_stock_return_price_arr[$row->inventory_id] + ($row->qty * $pre_os_grn_price)
                    : ($row->qty * $pre_os_grn_price);
            }
        }

        return [
            'pre_stock_return_arr' => $pre_stock_return_arr,
            'pre_stock_return_price_arr' => $pre_stock_return_price_arr
        ];
    }

    public function get_close_rfq_id_rfq_ids_against_inventory_rfq_qty($invarrs, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('MAX(id) AS id, MAX(rfq_id) AS rfq_id, MAX(inventory_id) AS inventory_id, MAX(quantity) AS quantity, MAX(buyer_rfq_status) AS buyer_rfq_status')
            ->from('tbl_rfq')
            ->where_in('inventory_id', $invarrs)
            ->where([
                'record_type' => '2',
                'inv_status' => '1'
            ]);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_at >=', $new_from_date);
            $this->db->where('updated_at <=', $new_to_date);
        }

        $this->db->group_by('variant_grp_id');

        $query = $this->db->get();

        $close_rfq_id_arr = [];
        $rfq_ids_against_inventory_id = [];
        $rfq_qty = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                if ($row->buyer_rfq_status == 8 || $row->buyer_rfq_status == 10) {
                    // Store closed RFQ IDs
                    $close_rfq_id_arr[$row->id] = $row->id;
                    $rfq_ids_against_inventory_id[$row->id] = $row->inventory_id;
                } else {
                    // Sum quantities for active RFQs
                    $rfq_qty[$row->inventory_id] = isset($rfq_qty[$row->inventory_id])
                        ? $rfq_qty[$row->inventory_id] + $row->quantity
                        : $row->quantity;
                }
            }
        }

        return [
            'close_rfq_id_arr' => $close_rfq_id_arr,
            'rfq_ids_against_inventory_id' => $rfq_ids_against_inventory_id,
            'rfq_qty' => $rfq_qty
        ];
    }

    public function get_rfq_tot_price_rfq_tot_price_inv($invarrs, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('id, rfq_id, inventory_id, quantity, buyer_rfq_status')
            ->from('tbl_rfq')
            ->where_in('inventory_id', $invarrs)
            ->where([
                'record_type' => '2',
                'inv_status' => '1'
            ]);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_at >=', $new_from_date);
            $this->db->where('updated_at <=', $new_to_date);
        }

        $query = $this->db->get();

        $rfq_tot_price_id = [];
        $rfq_tot_price_inv_id = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $rfq_tot_price_id[$row->id] = $row->id;
                $rfq_tot_price_inv_id[$row->id] = $row->inventory_id;
            }
        }

        return [
            'rfq_tot_price_id' => $rfq_tot_price_id,
            'rfq_tot_price_inv_id' => $rfq_tot_price_inv_id
        ];
    }

    public function get_close_price_get_inv_ids_price($close_rfq_id_arr, $new_from_date = null, $new_to_date = null) {
        if (empty($close_rfq_id_arr)) {
            return [];
        }

        $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $close_rfq_id_arr);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_date >=', $new_from_date);
            $this->db->where('updated_date <=', $new_to_date);
        }

        $query = $this->db->get();

        $close_price_ids = [];
        $get_inv_ids_price = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $close_price_ids[$row->id] = $row->id;
                $get_inv_ids_price[$row->id] = isset($rfq_ids_against_inventory_id[$row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$row->rfq_record_id] : '';
            }
        }

        return [
            'close_price_ids' => $close_price_ids,
            'get_inv_ids_price' => $get_inv_ids_price
        ];
    }

    public function get_order_price_place_order_inv_ids_price($rfq_tot_price_id, $rfq_tot_price_inv_id, $new_from_date = null, $new_to_date = null) {
        if (empty($rfq_tot_price_id)) {
            return [];
        }

        $this->db->select('id, rfq_record_id')
            ->from('tbl_rfq_price')
            ->where_in('rfq_record_id', $rfq_tot_price_id);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_date >=', $new_from_date);
            $this->db->where('updated_date <=', $new_to_date);
        }

        $query = $this->db->get();

        $order_price_ids = [];
        $place_order_inv_ids_price = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $order_price_ids[$row->id] = $row->id;
                $place_order_inv_ids_price[$row->id] = isset($rfq_tot_price_inv_id[$row->rfq_record_id]) ? $rfq_tot_price_inv_id[$row->rfq_record_id] : '';
            }
        }

        return [
            'order_price_ids' => $order_price_ids,
            'place_order_inv_ids_price' => $place_order_inv_ids_price
        ];
    }

    public function get_place_order_final_place_order_stock($order_price_ids, $place_order_inv_ids_price, $new_from_date = null, $new_to_date = null) {
        if (empty($order_price_ids)) {
            return [];
        }

        $this->db->select('price_id, order_quantity')
            ->from('tbl_rfq_order')
            ->where_in('price_id', $order_price_ids)
            ->where('order_status', '1');

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('updated_at >=', $new_from_date);
            $this->db->where('updated_at <=', $new_to_date);
        }

        $query = $this->db->get();
        $place_order = [];
        $final_place_order = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $rfq_ord) {
                $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id])
                    ? ($place_order[$rfq_ord->price_id] + $rfq_ord->order_quantity)
                    : $rfq_ord->order_quantity;
            }

            foreach ($place_order as $price_id => $order_qty) {
                if (isset($place_order_inv_ids_price[$price_id])) {
                    $inv_id = $place_order_inv_ids_price[$price_id];
                    $final_place_order[$inv_id] = isset($final_place_order[$inv_id])
                        ? ($final_place_order[$inv_id] + $order_qty)
                        : $order_qty;
                }
            }
        }

        return [
            'place_order' => $place_order,
            'final_place_order' => $final_place_order
        ];
    }

    public function get_new_grn_wpo($invarrs, $new_from_date = null, $new_to_date = null) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('SUM(grn_qty) AS total_grn_quantity, MAX(inventory_id) AS inventory_id')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->where([
                'inv_status' => 1,
                'is_deleted' => '0'
            ])
            ->group_by('inventory_id');

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $query = $this->db->get();
        $new_grn_wpo_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $grn_wp_res) {
                $new_grn_wpo_arr[$grn_wp_res->inventory_id] = $grn_wp_res->total_grn_quantity;
            }
        }

        return $new_grn_wpo_arr;
    }

    public function get_grn_stock_grn_stock_price($invarrs, $new_from_date = null, $new_to_date = null, $grn_price_arr = []) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $this->db->select('id, grn_qty, inventory_id, stock_return_for')
            ->from('grn_mgt')
            ->where([
                'grn_type' => '3',
                'is_deleted' => '0'
            ]);

        $query = $this->db->get();

        $grn_stock_arr = [];
        $grn_stock_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $grn_stock) {
                // Calculate total GRN quantity per inventory ID
                $grn_stock_arr[$grn_stock->inventory_id] = isset($grn_stock_arr[$grn_stock->inventory_id])
                    ? $grn_stock_arr[$grn_stock->inventory_id] + $grn_stock->grn_qty
                    : $grn_stock->grn_qty;

                // Determine the applicable GRN price
                $os_grn_price = 0;
                if ($grn_stock->stock_return_for == 0) {
                    $os_grn_price = isset($grn_price_arr['os'][$grn_stock->inventory_id])
                        ? $grn_price_arr['os'][$grn_stock->inventory_id]
                        : 0;
                } else {
                    $os_grn_price = isset($grn_price_arr[$grn_stock->stock_return_for])
                        ? round($grn_price_arr[$grn_stock->stock_return_for], 2)
                        : 0;
                }

                // Calculate total GRN price per inventory ID
                $grn_stock_price_arr[$grn_stock->inventory_id] = isset($grn_stock_price_arr[$grn_stock->inventory_id])
                    ? $grn_stock_price_arr[$grn_stock->inventory_id] + ($grn_stock->grn_qty * round($os_grn_price, 2))
                    : $grn_stock->grn_qty * round($os_grn_price, 2);
            }
        }

        return [
            'grn_stock_arr' => $grn_stock_arr,
            'grn_stock_price_arr' => $grn_stock_price_arr
        ];
    }

    public function get_issued_issued_price($invarrs, $new_from_date = null, $new_to_date = null, $grn_price_arr = []) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $this->db->select('qty, inventory_id, issued_return_for')
            ->from('issued_mgt')
            ->where('is_deleted', '0');

        $query = $this->db->get();

        $issued_arr = [];
        $issued_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $issue_res) {
                // Calculate total issued quantity per inventory ID
                $issued_arr[$issue_res->inventory_id] = isset($issued_arr[$issue_res->inventory_id])
                    ? $issued_arr[$issue_res->inventory_id] + $issue_res->qty
                    : $issue_res->qty;

                // Determine the applicable GRN price
                $os_grn_price = 0;
                if ($issue_res->issued_return_for == 0) {
                    $os_grn_price = isset($grn_price_arr['os'][$issue_res->inventory_id])
                        ? round($grn_price_arr['os'][$issue_res->inventory_id], 2)
                        : 0;
                } else {
                    $os_grn_price = isset($grn_price_arr[$issue_res->issued_return_for])
                        ? round($grn_price_arr[$issue_res->issued_return_for], 2)
                        : 0;
                }

                // Calculate total issued price per inventory ID
                $issued_price_arr[$issue_res->inventory_id] = isset($issued_price_arr[$issue_res->inventory_id])
                    ? $issued_price_arr[$issue_res->inventory_id] + ($issue_res->qty * $os_grn_price)
                    : $issue_res->qty * $os_grn_price;
            }
        }

        return [
            'issued_arr' => $issued_arr,
            'issued_price_arr' => $issued_price_arr
        ];
    }


    public function get_grn_wpo_grn_wpo_price($invarrs, $new_from_date = null, $new_to_date = null, $wpo_price = []) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->select('id, grn_qty, inventory_id, po_number, grn_buyer_rate')
            ->from('grn_mgt')
            ->where_in('inventory_id', $invarrs)
            ->where_in('grn_type', ['1', '4'])
            ->where('is_deleted', '0');

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $query = $this->db->get();
        $grn_wpo_arr = [];
        $grn_wpo_price_arr = [];
        $grn_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $grn_wp_res) {
                // Calculate total GRN quantity per inventory ID
                $grn_wpo_arr[$grn_wp_res->inventory_id] = isset($grn_wpo_arr[$grn_wp_res->inventory_id])
                    ? $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty
                    : $grn_wp_res->grn_qty;

                // Determine the applicable price
                $per_price = isset($wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id])
                    ? $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id]
                    : 0;
                if ($grn_wp_res->grn_buyer_rate > 0) {
                    $per_price = $grn_wp_res->grn_buyer_rate;
                }

                // Calculate total GRN price per inventory ID
                $grn_wpo_price_arr[$grn_wp_res->inventory_id] = isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])
                    ? $grn_wpo_price_arr[$grn_wp_res->inventory_id] + round($grn_wp_res->grn_qty * round($per_price, 2), 2)
                    : round($grn_wp_res->grn_qty * round($per_price, 2), 2);

                // Store per price for each GRN entry
                $grn_price_arr[$grn_wp_res->id] = $per_price;
            }
        }

        return [
            'grn_wpo_arr' => $grn_wpo_arr,
            'grn_wpo_price_arr' => $grn_wpo_price_arr,
            'grn_price_arr' => $grn_price_arr
        ];
    }

    public function get_issued_return_issued_return_price($invarrs, $new_from_date = null, $new_to_date = null, $grn_price_arr = []) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $this->db->select('qty, inventory_id, issued_return_for')
            ->from('issued_return_mgt')
            ->where('is_deleted', '0');

        $query = $this->db->get();

        $issued_return_arr = [];
        $issued_return_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $issue_ret_res) {
                // Calculate total issued return quantity per inventory ID
                $issued_return_arr[$issue_ret_res->inventory_id] = isset($issued_return_arr[$issue_ret_res->inventory_id])
                    ? $issued_return_arr[$issue_ret_res->inventory_id] + $issue_ret_res->qty
                    : $issue_ret_res->qty;

                // Determine the applicable GRN price
                $os_grn_price = 0;
                if ($issue_ret_res->issued_return_for == 0) {
                    $os_grn_price = isset($grn_price_arr['os'][$issue_ret_res->inventory_id])
                        ? round($grn_price_arr['os'][$issue_ret_res->inventory_id], 2)
                        : 0;
                } else {
                    $os_grn_price = isset($grn_price_arr[$issue_ret_res->issued_return_for])
                        ? round($grn_price_arr[$issue_ret_res->issued_return_for], 2)
                        : 0;
                }

                // Calculate total issued return price per inventory ID
                $issued_return_price_arr[$issue_ret_res->inventory_id] = isset($issued_return_price_arr[$issue_ret_res->inventory_id])
                    ? $issued_return_price_arr[$issue_ret_res->inventory_id] + ($issue_ret_res->qty * $os_grn_price)
                    : round($issue_ret_res->qty * $os_grn_price, 2);
            }
        }

        return [
            'issued_return_arr' => $issued_return_arr,
            'issued_return_price_arr' => $issued_return_price_arr
        ];
    }

    public function get_stock_return_stock_return_price($invarrs, $new_from_date = null, $new_to_date = null, $grn_price_arr = []) {
        if (empty($invarrs)) {
            return [];
        }

        $this->db->where_in('inventory_id', $invarrs);

        if (!empty($new_from_date) && !empty($new_to_date)) {
            $this->db->where('last_updated_date >=', $new_from_date);
            $this->db->where('last_updated_date <=', $new_to_date);
        }

        $this->db->select('qty, inventory_id, stock_return_for')
            ->from('tbl_return_stock')
            ->where('is_deleted', '0');

        $query = $this->db->get();

        $stock_return_arr = [];
        $stock_return_price_arr = [];

        if ($query->num_rows() > 0) {
            foreach ($query->result() as $stock_ret_res) {
                // Aggregate stock return quantity
                $stock_return_arr[$stock_ret_res->inventory_id] = isset($stock_return_arr[$stock_ret_res->inventory_id])
                    ? $stock_return_arr[$stock_ret_res->inventory_id] + $stock_ret_res->qty
                    : $stock_ret_res->qty;

                // Determine applicable GRN price
                $os_grn_price = 0;
                if ($stock_ret_res->stock_return_for == 0) {
                    $os_grn_price = isset($grn_price_arr['os'][$stock_ret_res->inventory_id])
                        ? round($grn_price_arr['os'][$stock_ret_res->inventory_id], 2)
                        : 0;
                } else {
                    $os_grn_price = isset($grn_price_arr[$stock_ret_res->stock_return_for])
                        ? round($grn_price_arr[$stock_ret_res->stock_return_for], 2)
                        : 0;
                }

                // Aggregate stock return price
                $stock_return_price_arr[$stock_ret_res->inventory_id] = isset($stock_return_price_arr[$stock_ret_res->inventory_id])
                    ? $stock_return_price_arr[$stock_ret_res->inventory_id] + round($stock_ret_res->qty * $os_grn_price, 2)
                    : round($stock_ret_res->qty * $os_grn_price, 2);
            }
        }

        return [
            'stock_return_arr' => $stock_return_arr,
            'stock_return_price_arr' => $stock_return_price_arr
        ];
    }

    public function get_pre_inventory_stock_report($stockfor)
    {
        $stock_inv_id   =   array('0');
        if($stockfor){
             $stock_form_date    =   $this->input->post('stock_form_date',true);
            $stock_to_date      =   $this->input->post('stock_to_date',true);
            if(isset($stock_form_date) && $stock_form_date!="" && isset($stock_to_date) && $stock_to_date!=""){
                $stock_form_date_arr    =   explode('/',$stock_form_date);
                $stock_to_date_arr      =   explode('/',$stock_to_date);
                $new_from_date          =   $stock_form_date_arr[2].'-'.$stock_form_date_arr[1].'-'.$stock_form_date_arr[0].' 00:00:00';
                $new_to_date            =   $stock_to_date_arr[2].'-'.$stock_to_date_arr[1].'-'.$stock_to_date_arr[0].' 23:59:59';
            }

        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }

        $buyer_users    =  getBuyerUserIdByParentId($users_ids);
        $result         =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id,$get_filter_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id,$get_filter_id);
        $invarrs        =   array();
        $grn_price_arr  =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]         =   $resp_val->id;
                $grn_price_arr['os'][$resp_val->id]   =   $resp_val->stock_price;
            }
            //===Total Indent Qty ===//
                    // $this->db->where_in('inventory_id',$invarrs);
                    // $this->db->group_by('inventory_id');
                    // $ind_qry = $this->db->select('inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));

                    // if($ind_qry->num_rows()){
                    //     foreach($ind_qry->result() as $inds_resp){
                    //         $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                    //     }
                    // }
            //===Total Indent Qty ===//

        }

        $data1      =   [];

        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){
                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//

            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number] = $rp_row->order_price;
                }
            }
            //====wpo price===//
            //===GRN====//
            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number];
                    }
                    else{
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty*$wpo_price[$grn_wp_res->po_number];
                    }
                    $grn_price_arr[$grn_wp_res->id] =   $wpo_price[$grn_wp_res->po_number];
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//

            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*($grn_wop_res->rate));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*($grn_wop_res->rate);
                    }
                    $grn_price_arr[$grn_wop_res->id] =   $grn_wop_res->rate;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $grn_stock_price_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '3', 'is_deleted' => '0'));
            $qry_grn_stock = $this->db->select('id,grn_qty,inventory_id,stock_return_for')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    if(isset($grn_stock_arr[$grn_stock->inventory_id])){
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock_arr[$grn_stock->inventory_id]+$grn_stock->grn_qty;
                    }
                    else{
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty;
                    }
                    if(isset($grn_stock_price_arr[$grn_stock->inventory_id])){
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$grn_stock->stock_return_for];
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock_price_arr[$grn_stock->inventory_id]+($grn_stock->grn_qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$grn_stock->stock_return_for];
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty*$os_grn_price;
                    }
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $issued_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            $qry_issued = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    if(isset($issued_arr[$issue_res->inventory_id])){
                        $issued_arr[$issue_res->inventory_id]    =   $issued_arr[$issue_res->inventory_id]+$issue_res->qty;
                    }
                    else{
                        $issued_arr[$issue_res->inventory_id]    =   $issue_res->qty;
                    }
                    if(isset($issued_price_arr[$grn_stock->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_res->issued_return_for];
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   ($issued_price_arr[$issue_res->inventory_id])+($issue_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_res->issued_return_for];
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   $issue_res->qty*$os_grn_price;
                    }
                }
            }
            //===Issued===//
            // pr($issued_price_arr);die;
            //====Issued Return===//
            $issued_return_arr = array();
            $issued_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            $qry_issued_return = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    if(isset($issued_return_arr[$issue_ret_res->inventory_id])){
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   ($issued_return_arr[$issue_ret_res->inventory_id])+($issue_ret_res->qty);
                    }
                    else{
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->qty;
                    }
                    if(isset($issued_return_price_arr[$issue_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_res->inventory_id]    =   ($issued_return_price_arr[$issue_res->inventory_id])+($issue_ret_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_res->inventory_id]    =   $issue_ret_res->qty*$os_grn_price;
                    }
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $stock_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            //$this->db->group_by('inventory_id');
            //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            $qry_stock_return = $this->db->select('qty,inventory_id,stock_return_for')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    if(isset($stock_return_arr[$stock_ret_res->inventory_id])){
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_return_arr[$stock_ret_res->inventory_id]+$stock_ret_res->qty;
                    }
                    else{
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty;
                    }
                    if(isset($stock_return_price_arr[$stock_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    =   ($stock_return_price_arr[$stock_ret_res->inventory_id])+($stock_ret_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty*$os_grn_price;
                    }
                }
            }
            //===Stock Return=====//
        }
            // pr($check_grn_inven_details);
            // pr($issued_inven_details2);
            // pr($with_po_price);
            // pr($new_without_po_price);
            // pr($without_po_price);
            // pr($stock_inven_details1);
            // pr($stock_inven_details);
            $check_grn_inven_details2 =array();
            $final_data =   array();
            $i=0;
            foreach ($result as $key => $val) {
            //===Indent Qty==//
                //$total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_price = 0;
            if(isset($grn_wpo_price_arr[$val->id])){
                $grn_qty_price = $grn_wpo_price_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_wop_price = 0;
            if(isset($grn_wopo_price_arr[$val->id])){
                $grn_qty_wop_price = $grn_wopo_price_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }

            $grn_qty_stok_price = 0;
            if(isset($grn_stock__price_arr[$val->id])){
                $grn_qty_stok_price = $grn_stock__price_arr[$val->id];
            }

            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            $issued_qty_price = 0;
            if(isset($issued_price_arr[$val->id])){
                $issued_qty_price = $issued_price_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            $issued_return_qty_price = 0;
            if(isset($issued_return_price_arr[$val->id])){
                $issued_return_qty_price = $issued_return_price_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            $stock_return_qty_price = 0;
            if(isset($stock_return_price_arr[$val->id])){
                $stock_return_qty_price = $stock_return_price_arr[$val->id];
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $opening_stock_price = $val->opening_stock*$val->stock_price;
            $mystock            =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock_price      =   ($opening_stock_price+$grn_qty_price+$grn_qty_wop_price+$grn_qty_stok_price+$issued_return_qty_price)-($issued_qty_price+$stock_return_qty_price);

             if($stockfor==1 && $mystock==0){
                    $stock_inv_id[]=$val->id;
                }
                if($stockfor==2 && $mystock>0){
                    $stock_inv_id[]=$val->id;
                }
                // $i++;
        }

            // foreach ($result as $key => $val) {
            //     $final_opening_stocks = ($val->opening_stock + $issued_return_inven_details2[$val->id][0]) - ($issued_inven_details2[$val->id][0] + $stock_inven_details[$val->id][0]);
            //     foreach($check_grn_inven_details as $keys=>$vals){
            //         $check_grn_inven_details2[$keys] = ($vals + $issued_return_inven_details2[$val->id][$keys])- ($issued_inven_details2[$val->id][$keys] + $stock_inven_details[$val->id][$keys]) ;
            //     }
            //     // pr($final_ammount_data);
            //     // echo 'inven => '.$val->id;
            //     // pr($check_grn_inven_details2);
            //     $final_ammount_data =  $final_opening_stocks * $val->stock_price;
            //     foreach($new_without_po_price[$val->id] as $k1 => $values1){
            //         $final_ammount_data += $values1 * $check_grn_inven_details2[$k1];
            //     }
            //     // pr($final_ammount_data);die;
            //     $never_order = 0;
            //     $order_quan_count = 0;
            //     $tot_order_quan_count = 0;
            //     if(!in_array($val->id,$no_inven_data)){
            //         $never_order = 1;
            //     }
            //     if(isset($order_inven_details[$val->id])){
            //         $order_quan_count = $order_inven_details[$val->id];
            //     }
            //     if(isset($total_order_inven_details[$val->id])){
            //         $tot_order_quan_count=$total_order_inven_details[$val->id];
            //     }
            //     $grn_qty = 0;
            //     if(isset($grn_inven_details[$val->id])){
            //         $grn_qty = $grn_inven_details[$val->id];
            //     }
            //     $total_grn_qty = 0;
            //     if(isset($total_grn_inven_details[$val->id])){
            //         $total_grn_qty=$total_grn_inven_details[$val->id];
            //     }
            //     $issued_qty =   0;
            //     if(isset($issued_inven_details[$val->id])){
            //         $issued_qty = $issued_inven_details[$val->id];
            //     }
            //     $stock_return_qty =   0;
            //     if(isset($stock_inven_details1[$val->id])){
            //         $stock_return_qty = $stock_inven_details1[$val->id];
            //     }
            //     $issued_return_qty =   0;
            //     if(isset($issued_return_inven_details[$val->id])){
            //         $issued_return_qty = $issued_return_inven_details[$val->id];
            //     }
            //     $without_po_ammount =   0;
            //     if(isset($total_grn_inven_details_without_po[$val->id])){
            //         $without_po_ammount = array_sum($total_grn_inven_details_without_po[$val->id]);
            //     }
            //     $sub_array = array();
            //     $mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty)-($stock_return_qty);
            //     if($stockfor==1 && $mystock==0){
            //         $stock_inv_id[]=$val->id;
            //     }
            //     if($stockfor==2 && $mystock>0){
            //         $stock_inv_id[]=$val->id;
            //     }
            //     $i++;
            // }
        }
        return $stock_inv_id;
    }

    public function export_stock_report(){
        $get_filter_id = array();
        if($_POST['stock_qty'] && $_POST['stock_qty']!=""){
            $get_filter_id  =   $this->get_pre_inventory_stock_report($_POST['stock_qty']);
        }
        $stock_form_date    =   $this->input->post('stock_form_date',true);
        $stock_to_date      =   $this->input->post('stock_to_date',true);
        if(isset($stock_form_date) && $stock_form_date!="" && isset($stock_to_date) && $stock_to_date!=""){
            $stock_form_date_arr    =   explode('/',$stock_form_date);
            $stock_to_date_arr      =   explode('/',$stock_to_date);
            $new_from_date          =   $stock_form_date_arr[2].'-'.$stock_form_date_arr[1].'-'.$stock_form_date_arr[0].' 00:00:00';
            $new_to_date            =   $stock_to_date_arr[2].'-'.$stock_to_date_arr[1].'-'.$stock_to_date_arr[0].' 23:59:59';
        }

        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users    =  getBuyerUserIdByParentId($users_ids);
        $result         =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id,$get_filter_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id,$get_filter_id);
        $invarrs        =   array();
        $grn_price_arr  =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]             =   $resp_val->id;
                $grn_price_arr['os'][$resp_val->id] =   $resp_val->stock_price;
            }
            //===Total Indent Qty ===//
                    // $this->db->where_in('inventory_id',$invarrs);
                    // $this->db->group_by('inventory_id');
                    // $ind_qry = $this->db->select('inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));

                    // if($ind_qry->num_rows()){
                    //     foreach($ind_qry->result() as $inds_resp){
                    //         $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                    //     }
                    // }
            //===Total Indent Qty ===//

        }

        $data1      =   [];

        if(isset($invarrs) && !empty($invarrs)){
            if(isset($new_from_date) && isset($new_to_date)){
                //====Pre Cureent Stock===//
                //====TOTAL RFQ===//
                $pre_rfq_qty                        =   array();
                $pre_close_rfq_id_arr               =   array();
                $pre_rfq_ids_against_inventory_id   =   array();
                $pre_rfq_tot_price_id               =   array();
                $pre_rfq_tot_price_inv_id           =   array();
                $this->db->group_by('variant_grp_id');
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at<',$new_from_date);
                }
                $pre_rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                if($pre_rfq_qry->num_rows()){
                    foreach($pre_rfq_qry->result() as $pre_rfq_rows){
                        if($pre_rfq_rows->buyer_rfq_status==8 || $pre_rfq_rows->buyer_rfq_status==10){
                            $pre_close_rfq_id_arr[$pre_rfq_rows->id]    =   $pre_rfq_rows->id;
                            $pre_rfq_ids_against_inventory_id[$pre_rfq_rows->id] = $pre_rfq_rows->inventory_id;
                        }else{
                            $pre_rfq_qty[$pre_rfq_rows->inventory_id] = isset($pre_rfq_qty[$pre_rfq_rows->inventory_id]) ? ($pre_rfq_qty[$pre_rfq_rows->inventory_id] + $pre_rfq_rows->quantity) : ($pre_rfq_rows->quantity);
                        }
                    }
                }
                //pr($pre_rfq_qty); die;
                //===For order RFQ===//
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at<',$new_from_date);
                }
                $pre_orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                if($pre_orfq_qry->num_rows()){
                    foreach($pre_orfq_qry->result() as $pre_rfq_rows){
                        $pre_rfq_tot_price_id[$pre_rfq_rows->id]        =   $pre_rfq_rows->id;
                        $pre_rfq_tot_price_inv_id[$pre_rfq_rows->id]    =   $pre_rfq_rows->inventory_id;
                    }
                }
                //===For Order RFQ===//
                //====TOTAL RFQ===//
                //===Closed RFQ Qty=====//
                $pre_close_price_ids    =   array();
                $pre_closed_order       =   array();
                $pre_final_close_order  =   array();
                $pre_get_inv_ids_price  =   array();
                if(isset($pre_close_rfq_id_arr) && !empty($pre_close_rfq_id_arr)){
                    $this->db->where_in('rfq_record_id',$pre_close_rfq_id_arr);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_date<',$new_from_date);
                    }
                    $pre_close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                    if($pre_close_qry_rfq_price->num_rows()){
                        foreach($pre_close_qry_rfq_price->result() as $pre_rfq_prc_row){
                            $pre_close_price_ids[$pre_rfq_prc_row->id] = $pre_rfq_prc_row->id;
                            $pre_get_inv_ids_price[$pre_rfq_prc_row->id] = isset($pre_rfq_ids_against_inventory_id[$pre_rfq_prc_row->rfq_record_id]) ? $pre_rfq_ids_against_inventory_id[$pre_rfq_prc_row->rfq_record_id] : '';
                        }
                    }
                }
                if(isset($pre_close_price_ids) && !empty($pre_close_price_ids)){
                    $this->db->where_in('price_id',$pre_close_price_ids);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_at<',$new_from_date);
                    }
                    $pre_qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                    if($pre_qry_rfq_order->num_rows()){
                        foreach($pre_qry_rfq_order->result() as $pre_rfq_ord){
                            $pre_closed_order[$pre_rfq_ord->price_id] = isset($pre_closed_order[$pre_rfq_ord->price_id]) ? $pre_closed_order[$pre_rfq_ord->price_id]+$pre_rfq_ord->order_quantity : $pre_rfq_ord->order_quantity;
                        }
                        foreach($pre_closed_order as $pre_crows_key => $pre_crow_val){
                            $pre_final_close_order[$pre_get_inv_ids_price[$pre_crows_key]] = $pre_crow_val;
                        }
                    }
                }
                //===Closed RFQ Qty=====//
                //===Place Order====//
                $pre_order_price_ids            =   array();
                $pre_place_order_inv_ids_price  =   array();
                $pre_place_order                =   array();
                $pre_final_place_order          =   array();
                //pr($pre_rfq_tot_price_id); die;
                if(isset($pre_rfq_tot_price_id) && !empty($pre_rfq_tot_price_id)){
                    $this->db->where_in('rfq_record_id',$pre_rfq_tot_price_id);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_date<',$new_from_date);
                    }
                    $pre_ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                    if($pre_ord_qry_rfq_price->num_rows()){
                        foreach($pre_ord_qry_rfq_price->result() as $pre_rfq_prc_row){
                            $pre_order_price_ids[$pre_rfq_prc_row->id] = $pre_rfq_prc_row->id;
                            $pre_place_order_inv_ids_price[$pre_rfq_prc_row->id] = isset($pre_rfq_tot_price_inv_id[$pre_rfq_prc_row->rfq_record_id]) ? $pre_rfq_tot_price_inv_id[$pre_rfq_prc_row->rfq_record_id] : '';
                        }
                    }
                }
                if(isset($pre_order_price_ids) && !empty($pre_order_price_ids)){
                    $this->db->where_in('price_id',$pre_order_price_ids);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_at<',$new_from_date);
                    }
                    $pre_qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                    if($pre_qry_rfq_placeorder->num_rows()){
                        foreach($pre_qry_rfq_placeorder->result() as $pre_rfq_ord){
                            $pre_place_order[$pre_rfq_ord->price_id] = isset($pre_place_order[$pre_rfq_ord->price_id]) ? $pre_place_order[$pre_rfq_ord->price_id]+$pre_rfq_ord->order_quantity : $pre_rfq_ord->order_quantity;
                        }
                        foreach($pre_place_order as $pre_crows_key => $pre_crow_val){
                            $pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]] = isset($pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]]) ? ($pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]] + $pre_crow_val) : $pre_crow_val;
                        }
                    }
                }
                //pr($final_place_order); die;
                //===Place Order====//
                //====Wpo price===//
                $pre_wpo_price = array();
                $this->db->where_in('inventory_id',$invarrs);

                $pre_qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
                if($pre_qry_rfq_price->num_rows()){
                    foreach($pre_qry_rfq_price->result() as $pre_rp_row){
                        $pre_wpo_price[$pre_rp_row->po_number][$pre_rp_row->inventory_id] = $pre_rp_row->order_price;
                    }
                }
                $pre_qry_rfq_price = $this->db->get_where('tbl_manual_po_order',array('product_price !=' => ''));
                if($pre_qry_rfq_price->num_rows()){
                    foreach($pre_qry_rfq_price->result() as $pre_rp_row){
                        $pre_wpo_price[$pre_rp_row->manual_po_number][$pre_rp_row->inventory_id] = $pre_rp_row->product_price;
                    }
                }
                //pr($pre_wpo_price); die;
                //====wpo price===//
                //===GRN====//

                $pre_new_grn_wpo_arr =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date>',$new_from_date);
                }
                $this->db->group_by('inventory_id');
                $pre_new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')
                ->where_in('grn_type', array('1', '4'))
                ->get_where('grn_mgt',array('inv_status' => 1,
                // 'grn_type' => '1',
                'is_deleted' => '0'));
                // echo $this->db->last_query();die('test');
                if($pre_new_qry_grn_wp->num_rows()){
                    foreach($pre_new_qry_grn_wp->result() as $pre_grn_wp_res){
                        $pre_new_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->total_grn_quantity;
                    }
                }

                //===GRN WPO====//
                $pre_grn_wpo_arr        =   array();
                $pre_grn_wpo_price_arr  =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number')
                ->where_in('grn_type', array('1', '4'))
                ->get_where('grn_mgt',array(
                    // 'grn_type' => '1',
                    'is_deleted' => '0'));
                // echo $this->db->last_query(); die;/
                if($pre_qry_grn_wp->num_rows()){
                    foreach($pre_qry_grn_wp->result() as $pre_grn_wp_res){
                        if(isset($pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id])){
                            $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =  $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id] + $pre_grn_wp_res->grn_qty;
                        }
                        else{
                            $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->grn_qty;
                        }
                        if(isset($pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id])){
                            $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id]    =  $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id] + ($pre_grn_wp_res->grn_qty*$pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id]);
                        }
                        else{
                            $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->grn_qty*$pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id];
                        }
                        $pre_grn_price_arr[$pre_grn_wp_res->id] =   $pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id];
                    }
                }
                //pr($pre_grn_wpo_arr); die;
                //===GRN WPO====//
                //===GRN WOPO====//

                $pre_grn_wopo_arr           =   array();
                $pre_grn_wopo_price_arr     =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
                if($pre_qry_grn_wop->num_rows()){
                    foreach($pre_qry_grn_wop->result() as $pre_grn_wop_res){
                        if(isset($pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]+$pre_grn_wop_res->grn_qty;
                        }
                        else{
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wop_res->grn_qty;
                        }
                        if(isset($pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]+(($pre_grn_wop_res->grn_qty)*(round($pre_grn_wop_res->rate,2)));
                        }
                        else{
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   ($pre_grn_wop_res->grn_qty)*(round($pre_grn_wop_res->rate,2));
                        }
                        $pre_grn_price_arr[$pre_grn_wop_res->id] =   round($pre_grn_wop_res->rate,2);
                    }
                }
                //pr($pre_grn_wopo_arr); die;
                //===GRN WOPO====//
                //===Stock GRN===//
                $pre_grn_stock_arr =   array();
                $pre_grn_stock_price_arr =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_stock = $this->db->select('id,grn_qty,inventory_id,stock_return_for')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
                if($pre_qry_grn_stock->num_rows()){
                    foreach($pre_qry_grn_stock->result() as $pre_grn_stock){
                        if(isset($pre_grn_stock_arr[$pre_grn_stock->inventory_id])){
                            $pre_grn_stock_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock_arr[$pre_grn_stock->inventory_id]+$pre_grn_stock->grn_qty;
                        }
                        else{
                            $pre_grn_stock_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock->grn_qty;
                        }
                        if(isset($pre_grn_stock_price_arr[$pre_grn_stock->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_grn_stock->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_grn_stock->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_grn_stock->stock_return_for];
                            }
                            $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]+($pre_grn_stock->grn_qty*round($pre_os_grn_price,2));
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_grn_stock->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_grn_stock->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_grn_stock->stock_return_for];
                            }
                            $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]    =   $grn_stock->grn_qty*round($os_grn_price,2);
                        }
                    }
                }
                //===Stock GRN===//
                //===Issued===//
                $pre_issued_arr = array();
                $pre_issued_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                //$this->db->group_by('inventory_id');
                //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
                $pre_qry_issued = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_mgt',array('is_deleted' => '0'));
                if($pre_qry_issued->num_rows()){
                    foreach($pre_qry_issued->result() as $pre_issue_res){
                        if(isset($pre_issued_arr[$pre_issue_res->inventory_id])){
                            $pre_issued_arr[$pre_issue_res->inventory_id]    =   $pre_issued_arr[$pre_issue_res->inventory_id]+$pre_issue_res->qty;
                        }
                        else{
                            $pre_issued_arr[$pre_issue_res->inventory_id]    =   $pre_issue_res->qty;
                        }
                        if(isset($pre_issued_price_arr[$pre_issue_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_issue_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_res->issued_return_for];
                            }
                            $pre_issued_price_arr[$pre_issue_res->inventory_id]    =   ($pre_issued_price_arr[$pre_issue_res->inventory_id])+($pre_issue_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_issue_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_res->issued_return_for];
                            }
                            $pre_issued_price_arr[$pre_issue_res->inventory_id]    =   $pre_issue_res->qty*$pre_os_grn_price;
                        }
                    }
                }
                //===Issued===//
                //====Issued Return===//
                $pre_issued_return_arr = array();
                $pre_issued_return_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_issued_return = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_return_mgt',array('is_deleted' => '0'));
                if($pre_qry_issued_return->num_rows()){

                    foreach($pre_qry_issued_return->result() as $pre_issue_ret_res){
                        if(isset($pre_issued_return_arr[$pre_issue_ret_res->inventory_id])){
                            $pre_issued_return_arr[$pre_issue_ret_res->inventory_id]    =   ($pre_issued_return_arr[$pre_issue_ret_res->inventory_id])+($pre_issue_ret_res->qty);
                        }
                        else{
                            $pre_issued_return_arr[$pre_issue_ret_res->inventory_id]    =   $pre_issue_ret_res->qty;
                        }
                        if(isset($pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_issue_ret_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_ret_res->issued_return_for];
                            }
                            $pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id]    =   ($pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id])+($pre_issue_ret_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_issue_ret_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_ret_res->issued_return_for];
                            }
                            $pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id]    =   $pre_issue_ret_res->qty*$pre_os_grn_price;
                        }

                    }
                }
                // pr($issued_return_arr);
                // pr($issued_return_price_arr);die;
                //====Issued Return===//
                //===Stock Return=====//
                $pre_stock_return_arr = array();
                $pre_stock_return_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                //$this->db->group_by('inventory_id');
                //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
                $pre_qry_stock_return = $this->db->select('qty,inventory_id,stock_return_for')->get_where('tbl_return_stock',array('is_deleted' => '0'));
                if($pre_qry_stock_return->num_rows()){
                    foreach($pre_qry_stock_return->result() as $pre_stock_ret_res){
                        if(isset($pre_stock_return_arr[$pre_stock_ret_res->inventory_id])){
                            $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]+$pre_stock_ret_res->qty;
                        }
                        else{
                            $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_ret_res->qty;
                        }
                        if(isset($pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_stock_ret_res->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_stock_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_stock_ret_res->stock_return_for];
                            }
                            $pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id]    =   ($pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id])+($pre_stock_ret_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_stock_ret_res->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_stock_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_stock_ret_res->stock_return_for];
                            }
                            $pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_ret_res->qty*$pre_os_grn_price;
                        }
                    }
                }
                //===Stock Return=====//
                //====Pre Cureent Stock===//
            }
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('updated_at>=',$new_from_date);
                $this->db->where('updated_at<=',$new_to_date);
            }
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){
                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('updated_at>=',$new_from_date);
                $this->db->where('updated_at<=',$new_to_date);
            }
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_date>=',$new_from_date);
                    $this->db->where('updated_date<=',$new_to_date);
                }
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at>=',$new_from_date);
                    $this->db->where('updated_at<=',$new_to_date);
                }
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//

            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_date>=',$new_from_date);
                    $this->db->where('updated_date<=',$new_to_date);
                }
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at>=',$new_from_date);
                    $this->db->where('updated_at<=',$new_to_date);
                }
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);

            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number][$rp_row->inventory_id] = $rp_row->order_price;
                }
            }
            $qry_rfq_price = $this->db->get_where('tbl_manual_po_order',array('product_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->manual_po_number][$rp_row->inventory_id] = round($rp_row->product_price,2);
                }
            }
            //====wpo price===//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')
            ->where_in('grn_type', array('1', '4'))
            ->get_where('grn_mgt',array('inv_status' => 1,
            // 'grn_type' => '1',
            'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')
            ->where_in('grn_type', array('1', '4'))
            ->get_where('grn_mgt',array(
                // 'grn_type' => '1',
                 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + round($grn_wp_res->grn_qty*round($per_price,2),2);
                    }
                    else{
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   round($grn_wp_res->grn_qty*round($per_price,2),2);
                    }
                    $per_price_new = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    if($grn_wp_res->grn_buyer_rate>0){
                        $per_price_new = $grn_wp_res->grn_buyer_rate;
                    }
                    //$grn_price_arr[$grn_wp_res->id] =   $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    $grn_price_arr[$grn_wp_res->id] =   $per_price_new;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//

            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*(round($grn_wop_res->rate,2)));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*(round($grn_wop_res->rate,2));
                    }
                    $grn_price_arr[$grn_wop_res->id] =   round($grn_wop_res->rate,2);
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $grn_stock_price_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '3', 'is_deleted' => '0'));
            $qry_grn_stock = $this->db->select('id,grn_qty,inventory_id,stock_return_for')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    if(isset($grn_stock_arr[$grn_stock->inventory_id])){
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock_arr[$grn_stock->inventory_id]+$grn_stock->grn_qty;
                    }
                    else{
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty;
                    }
                    if(isset($grn_stock_price_arr[$grn_stock->inventory_id])){
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   round($grn_price_arr[$grn_stock->stock_return_for],2);
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock_price_arr[$grn_stock->inventory_id]+($grn_stock->grn_qty*round($os_grn_price,2));
                    }
                    else{
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   round($grn_price_arr[$grn_stock->stock_return_for],2);
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty*round($os_grn_price,2);
                    }
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $issued_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            $qry_issued = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    if(isset($issued_arr[$issue_res->inventory_id])){
                        $issued_arr[$issue_res->inventory_id]    =   $issued_arr[$issue_res->inventory_id]+$issue_res->qty;
                    }
                    else{
                        $issued_arr[$issue_res->inventory_id]    =   $issue_res->qty;
                    }
                    if(isset($issued_price_arr[$issue_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = round($grn_price_arr['os'][$issue_res->inventory_id],2);
                        }
                        else{
                            $os_grn_price   =   round($grn_price_arr[$issue_res->issued_return_for],2);
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   ($issued_price_arr[$issue_res->inventory_id])+($issue_res->qty*round($os_grn_price,2));
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_res->issued_return_for];
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   $issue_res->qty*round($os_grn_price,2);
                    }
                }
            }
            //===Issued===//
            //====Issued Return===//
            $issued_return_arr = array();
            $issued_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            $qry_issued_return = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){

                foreach($qry_issued_return->result() as $issue_ret_res){
                    if(isset($issued_return_arr[$issue_ret_res->inventory_id])){
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   ($issued_return_arr[$issue_ret_res->inventory_id])+($issue_ret_res->qty);
                    }
                    else{
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->qty;
                    }
                    if(isset($issued_return_price_arr[$issue_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_ret_res->inventory_id]    =   ($issued_return_price_arr[$issue_ret_res->inventory_id])+($issue_ret_res->qty*round($os_grn_price,2));
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_ret_res->inventory_id]    =   round($issue_ret_res->qty*round($os_grn_price,2),2);
                    }

                }
            }
            // pr($issued_return_arr);
            // pr($issued_return_price_arr);die;
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $stock_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            $mystok_price = array();
            $kk =0;
            //$this->db->group_by('inventory_id');
            //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            $qry_stock_return = $this->db->select('qty,inventory_id,stock_return_for')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    if(isset($stock_return_arr[$stock_ret_res->inventory_id])){
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_return_arr[$stock_ret_res->inventory_id]+$stock_ret_res->qty;
                    }
                    else{
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty;
                    }
                    if(isset($stock_return_price_arr[$stock_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    =   ($stock_return_price_arr[$stock_ret_res->inventory_id])+(round($stock_ret_res->qty*round($os_grn_price,2),2));
                    }
                    else{
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    = round($stock_ret_res->qty*round($os_grn_price,2),2);
                    }
                    // $mystok_price[$kk]['ret']=$stock_ret_res->stock_return_for;
                    // $mystok_price[$kk]['price']=$os_grn_price;
                    // $mystok_price[$kk]['qty']=$stock_ret_res->qty;
                    // $mystok_price[$kk]['val']=$stock_ret_res->qty*round($os_grn_price,2);
                    $kk++;
                }
            }
            //pr($mystok_price); //die;
            //pr($stock_return_price_arr); die;
            //===Stock Return=====//
        }
        // pr($grn_stock_arr);die;
        $sub_array  =   array();
        $data1      =   array();
        $final_data =   array();
        $data       =   array();
        $i = 0;
        foreach ($result as $key => $val) {
            //===Indent Qty==//
                //$total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_price = 0;
            if(isset($grn_wpo_price_arr[$val->id])){
                $grn_qty_price = round($grn_wpo_price_arr[$val->id],2);
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_wop_price = 0;
            if(isset($grn_wopo_price_arr[$val->id])){
                $grn_qty_wop_price = round($grn_wopo_price_arr[$val->id],2);
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }

            $grn_qty_stok_price = 0;
            if(isset($grn_stock_price_arr[$val->id])){
                $grn_qty_stok_price = round($grn_stock_price_arr[$val->id],2);
            }

            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            $issued_qty_price = 0;
            if(isset($issued_price_arr[$val->id])){
                $issued_qty_price = round($issued_price_arr[$val->id],2);
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            $issued_return_qty_price = 0;
            if(isset($issued_return_price_arr[$val->id])){
                $issued_return_qty_price = round($issued_return_price_arr[$val->id],2);
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            $stock_return_qty_price = 0;
            if(isset($stock_return_price_arr[$val->id])){
                $stock_return_qty_price = round($stock_return_price_arr[$val->id],2);
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $opening_stock_price = $val->opening_stock*round($val->stock_price,2);
            $mystock            =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock_price      =   (round($opening_stock_price,2)+round($grn_qty_price,2)+round($grn_qty_wop_price,2)+round($grn_qty_stok_price,2)+round($issued_return_qty_price,2))-(round($issued_qty_price,2)+round($stock_return_qty_price,2));
            if(isset($new_from_date) && isset($new_to_date)){
                //===pre GRN====//
                $pre_grn_qty = 0;
                if(isset($pre_grn_wpo_arr[$val->id])){
                    $pre_grn_qty = $pre_grn_wpo_arr[$val->id];
                }
                $pre_grn_qty_price = 0;
                if(isset($pre_grn_wpo_price_arr[$val->id])){
                    $pre_grn_qty_price = round($pre_grn_wpo_price_arr[$val->id],2);
                }
                $pre_grn_qty_wop = 0;
                if(isset($pre_grn_wopo_arr[$val->id])){
                    $pre_grn_qty_wop = $pre_grn_wopo_arr[$val->id];
                }
                $pre_grn_qty_wop_price = 0;
                if(isset($pre_grn_wopo_price_arr[$val->id])){
                    $pre_grn_qty_wop_price = round($pre_grn_wopo_price_arr[$val->id],2);
                }
                $pre_grn_qty_stok = 0;
                if(isset($pre_grn_stock_arr[$val->id])){
                    $pre_grn_qty_stok = $pre_grn_stock_arr[$val->id];
                }

                $pre_grn_qty_stok_price = 0;
                if(isset($pre_grn_stock_price_arr[$val->id])){
                    $pre_grn_qty_stok_price = round($pre_grn_stock_price_arr[$val->id],2);
                }

                //====GRN====//
                //===Issued=====//
                $pre_issued_qty = 0;
                if(isset($pre_issued_arr[$val->id])){
                    $pre_issued_qty = $pre_issued_arr[$val->id];
                }
                $pre_issued_qty_price = 0;
                if(isset($pre_issued_price_arr[$val->id])){
                    $pre_issued_qty_price = round($pre_issued_price_arr[$val->id],2);
                }
                //===Issued=====//
                //===Isseued Return==//
                $pre_issued_return_qty = 0;
                if(isset($pre_issued_arr[$val->id])){
                    $pre_issued_return_qty = $pre_issued_return_arr[$val->id];
                }
                $pre_issued_return_qty_price = 0;
                if(isset($pre_issued_return_price_arr[$val->id])){
                    $pre_issued_return_qty_price = round($pre_issued_return_price_arr[$val->id],2);
                }
                //===Issued Return===//
                //===Stock Return===//
                $pre_stock_return_qty = 0;
                if(isset($pre_stock_return_arr[$val->id])){
                    $pre_stock_return_qty = $pre_stock_return_arr[$val->id];
                }
                $pre_stock_return_qty_price = 0;
                if(isset($pre_stock_return_price_arr[$val->id])){
                    $pre_stock_return_qty_price = round($pre_stock_return_price_arr[$val->id],2);
                }
                //===Stock Return====//
                $mystock    =   ($mystock+$pre_grn_qty+$pre_grn_qty_wop+$pre_grn_qty_stok+$pre_issued_return_qty)-($pre_issued_qty+$pre_stock_return_qty);
                $mystock_price      =   (round($mystock_price,2)+round($pre_grn_qty_price,2)+round($pre_grn_qty_wop_price,2)+round($pre_grn_qty_stok_price,2)+round($pre_issued_return_qty_price,2))-(round($pre_issued_qty_price,2)+round($pre_stock_return_qty_price,2));
            }

            $final_data[$i]['Product']              =   $val->prod_name;
            $final_data[$i]['Our Product Name']     =   ($val->buyer_product_name);
            $final_data[$i]['Specification']        =   ($val->specification);
            $final_data[$i]['Size']                 =   ($val->size);
            $final_data[$i]['grp']                  =   ($val->inventory_grouping);
            $final_data[$i]['UOM']                  =   $val->uom_name;
            $final_data[$i]['current_stock_qty']    =   round($mystock,2);
            // $final_data[$i]['total_amount']         =   formatIndianRupees($mystock_price);
            $mystock_price  =   round($mystock_price,2);
            if($mystock_price>=1){
                $formatted_price = formatIndianRupees($mystock_price);
            }
            else{
                $formatted_price = $mystock_price >= '.01' ? $mystock_price : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            $final_data[$i]['total_amount']=$formatted_price;
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty = $issued_qty-$issued_return_qty;
                //$sub_array[] = round($orgnal_issued_qty,2);
                $final_data[$i]['issued_qty']       =   round($orgnal_issued_qty,2);
            }
            else{
                $final_data[$i]['issued_qty']       =   0;
            }
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty_price = round($issued_qty_price,2)-round($issued_return_qty_price,2);
                // $final_data[$i]['Issued_Amount'] = formatIndianRupees($orgnal_issued_qty_price);
                if($orgnal_issued_qty_price>=1){
                    $formatted_price = formatIndianRupees($orgnal_issued_qty_price);
                }
                else{
                    $formatted_price = $orgnal_issued_qty_price >= '.01' ? $orgnal_issued_qty_price : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['Issued_Amount']=$formatted_price;
            }
            else{
                $final_data[$i]['Issued_Amount'] = '0.00';
            }


            if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok>0){
                $totgrn         =   $grn_qty+$grn_qty_wop + $grn_qty_stok;
                $final_data[$i]['GRN_Qty']  =   round($totgrn,2);
            }
            else{
                $final_data[$i]['GRN_Qty'] = 0;
            }
            if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok_price>0){
                // $sub_array[] = $grn_qty_price;
                $grn_qty_price_final    =   round($grn_qty_price,2) + round($grn_qty_wop_price,2) + round($grn_qty_stok_price,2);
                // $final_data[$i]['GRN_Amount'] = formatIndianRupees($grn_qty_price_final);
                if($grn_qty_price_final>'1'){
                    $formatted_price = formatIndianRupees($grn_qty_price_final);
                }
                else{
                    $formatted_price = $grn_qty_price_final >= '.01' ? $grn_qty_price_final : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['GRN_Amount']=$formatted_price;
            }
            else{
                $final_data[$i]['GRN_Amount'] = '0.00';
            }
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function export_stock_report_oldssss()
    {
        $get_filter_id = array();
        if($_POST['stock_qty'] && $_POST['stock_qty']!=""){
            $get_filter_id  =   $this->get_pre_inventory_stock_report($_POST['stock_qty']);
        }
        $stock_form_date    =   $this->input->post('stock_form_date',true);
        $stock_to_date      =   $this->input->post('stock_to_date',true);
        if(isset($stock_form_date) && $stock_form_date!="" && isset($stock_to_date) && $stock_to_date!=""){
            $stock_form_date_arr    =   explode('/',$stock_form_date);
            $stock_to_date_arr      =   explode('/',$stock_to_date);
            $new_from_date          =   $stock_form_date_arr[2].'-'.$stock_form_date_arr[1].'-'.$stock_form_date_arr[0].' 00:00:00';
            $new_to_date            =   $stock_to_date_arr[2].'-'.$stock_to_date_arr[1].'-'.$stock_to_date_arr[0].' 23:59:59';
        }

        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users    =  getBuyerUserIdByParentId($users_ids);
        $result         =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id,$get_filter_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id,$get_filter_id);
        $invarrs        =   array();
        $grn_price_arr  =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]         =   $resp_val->id;
                $grn_price_arr['os'][$resp_val->id]   =   $resp_val->stock_price;
            }
            //===Total Indent Qty ===//
                    // $this->db->where_in('inventory_id',$invarrs);
                    // $this->db->group_by('inventory_id');
                    // $ind_qry = $this->db->select('inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));

                    // if($ind_qry->num_rows()){
                    //     foreach($ind_qry->result() as $inds_resp){
                    //         $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                    //     }
                    // }
            //===Total Indent Qty ===//

        }

        $data1      =   [];

        if(isset($invarrs) && !empty($invarrs)){
            if(isset($new_from_date) && isset($new_to_date)){
                //====Pre Cureent Stock===//
                //====TOTAL RFQ===//
                $pre_rfq_qty                        =   array();
                $pre_close_rfq_id_arr               =   array();
                $pre_rfq_ids_against_inventory_id   =   array();
                $pre_rfq_tot_price_id               =   array();
                $pre_rfq_tot_price_inv_id           =   array();
                $this->db->group_by('variant_grp_id');
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at<',$new_from_date);
                }
                $pre_rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                if($pre_rfq_qry->num_rows()){
                    foreach($pre_rfq_qry->result() as $pre_rfq_rows){
                        if($pre_rfq_rows->buyer_rfq_status==8 || $pre_rfq_rows->buyer_rfq_status==10){
                            $pre_close_rfq_id_arr[$pre_rfq_rows->id]    =   $pre_rfq_rows->id;
                            $pre_rfq_ids_against_inventory_id[$pre_rfq_rows->id] = $pre_rfq_rows->inventory_id;
                        }else{
                            $pre_rfq_qty[$pre_rfq_rows->inventory_id] = isset($pre_rfq_qty[$pre_rfq_rows->inventory_id]) ? ($pre_rfq_qty[$pre_rfq_rows->inventory_id] + $pre_rfq_rows->quantity) : ($pre_rfq_rows->quantity);
                        }
                    }
                }
                //pr($pre_rfq_qty); die;
                //===For order RFQ===//
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at<',$new_from_date);
                }
                $pre_orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
                if($pre_orfq_qry->num_rows()){
                    foreach($pre_orfq_qry->result() as $pre_rfq_rows){
                        $pre_rfq_tot_price_id[$pre_rfq_rows->id]        =   $pre_rfq_rows->id;
                        $pre_rfq_tot_price_inv_id[$pre_rfq_rows->id]    =   $pre_rfq_rows->inventory_id;
                    }
                }
                //===For Order RFQ===//
                //====TOTAL RFQ===//
                //===Closed RFQ Qty=====//
                $pre_close_price_ids    =   array();
                $pre_closed_order       =   array();
                $pre_final_close_order  =   array();
                $pre_get_inv_ids_price  =   array();
                if(isset($pre_close_rfq_id_arr) && !empty($pre_close_rfq_id_arr)){
                    $this->db->where_in('rfq_record_id',$pre_close_rfq_id_arr);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_date<',$new_from_date);
                    }
                    $pre_close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                    if($pre_close_qry_rfq_price->num_rows()){
                        foreach($pre_close_qry_rfq_price->result() as $pre_rfq_prc_row){
                            $pre_close_price_ids[$pre_rfq_prc_row->id] = $pre_rfq_prc_row->id;
                            $pre_get_inv_ids_price[$pre_rfq_prc_row->id] = isset($pre_rfq_ids_against_inventory_id[$pre_rfq_prc_row->rfq_record_id]) ? $pre_rfq_ids_against_inventory_id[$pre_rfq_prc_row->rfq_record_id] : '';
                        }
                    }
                }
                if(isset($pre_close_price_ids) && !empty($pre_close_price_ids)){
                    $this->db->where_in('price_id',$pre_close_price_ids);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_at<',$new_from_date);
                    }
                    $pre_qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                    if($pre_qry_rfq_order->num_rows()){
                        foreach($pre_qry_rfq_order->result() as $pre_rfq_ord){
                            $pre_closed_order[$pre_rfq_ord->price_id] = isset($pre_closed_order[$pre_rfq_ord->price_id]) ? $pre_closed_order[$pre_rfq_ord->price_id]+$pre_rfq_ord->order_quantity : $pre_rfq_ord->order_quantity;
                        }
                        foreach($pre_closed_order as $pre_crows_key => $pre_crow_val){
                            $pre_final_close_order[$pre_get_inv_ids_price[$pre_crows_key]] = $pre_crow_val;
                        }
                    }
                }
                //===Closed RFQ Qty=====//
                //===Place Order====//
                $pre_order_price_ids            =   array();
                $pre_place_order_inv_ids_price  =   array();
                $pre_place_order                =   array();
                $pre_final_place_order          =   array();
                //pr($pre_rfq_tot_price_id); die;
                if(isset($pre_rfq_tot_price_id) && !empty($pre_rfq_tot_price_id)){
                    $this->db->where_in('rfq_record_id',$pre_rfq_tot_price_id);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_date<',$new_from_date);
                    }
                    $pre_ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                    if($pre_ord_qry_rfq_price->num_rows()){
                        foreach($pre_ord_qry_rfq_price->result() as $pre_rfq_prc_row){
                            $pre_order_price_ids[$pre_rfq_prc_row->id] = $pre_rfq_prc_row->id;
                            $pre_place_order_inv_ids_price[$pre_rfq_prc_row->id] = isset($pre_rfq_tot_price_inv_id[$pre_rfq_prc_row->rfq_record_id]) ? $pre_rfq_tot_price_inv_id[$pre_rfq_prc_row->rfq_record_id] : '';
                        }
                    }
                }
                if(isset($pre_order_price_ids) && !empty($pre_order_price_ids)){
                    $this->db->where_in('price_id',$pre_order_price_ids);
                    if(isset($new_from_date) && isset($new_to_date)){
                        $this->db->where('updated_at<',$new_from_date);
                    }
                    $pre_qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                    if($pre_qry_rfq_placeorder->num_rows()){
                        foreach($pre_qry_rfq_placeorder->result() as $pre_rfq_ord){
                            $pre_place_order[$pre_rfq_ord->price_id] = isset($pre_place_order[$pre_rfq_ord->price_id]) ? $pre_place_order[$pre_rfq_ord->price_id]+$pre_rfq_ord->order_quantity : $pre_rfq_ord->order_quantity;
                        }
                        foreach($pre_place_order as $pre_crows_key => $pre_crow_val){
                            $pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]] = isset($pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]]) ? ($pre_final_place_order[$pre_place_order_inv_ids_price[$pre_crows_key]] + $pre_crow_val) : $pre_crow_val;
                        }
                    }
                }
                //pr($final_place_order); die;
                //===Place Order====//
                //====Wpo price===//
                $pre_wpo_price = array();
                $this->db->where_in('inventory_id',$invarrs);

                $pre_qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
                if($pre_qry_rfq_price->num_rows()){
                    foreach($pre_qry_rfq_price->result() as $pre_rp_row){
                        $pre_wpo_price[$pre_rp_row->po_number][$pre_rp_row->inventory_id] = $pre_rp_row->order_price;
                    }
                }
                //====wpo price===//
                //===GRN====//
                $pre_new_grn_wpo_arr =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $this->db->group_by('inventory_id');
                $pre_new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
                if($pre_new_qry_grn_wp->num_rows()){
                    foreach($pre_new_qry_grn_wp->result() as $pre_grn_wp_res){
                        $pre_new_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->total_grn_quantity;
                    }
                }
                //===GRN WPO====//
                $pre_grn_wpo_arr        =   array();
                $pre_grn_wpo_price_arr  =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
                //echo $this->db->last_query(); die;
                if($pre_qry_grn_wp->num_rows()){
                    foreach($pre_qry_grn_wp->result() as $pre_grn_wp_res){
                        if(isset($pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id])){
                            $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =  $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id] + $pre_grn_wp_res->grn_qty;
                        }
                        else{
                            $pre_grn_wpo_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->grn_qty;
                        }
                        if(isset($pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id])){
                            $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id]    =  $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id] + $pre_grn_wp_res->grn_qty*$pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id];
                        }
                        else{
                            $pre_grn_wpo_price_arr[$pre_grn_wp_res->inventory_id]    =   $pre_grn_wp_res->grn_qty*$pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id];
                        }
                        $pre_grn_price_arr[$pre_grn_wp_res->id] =   $pre_wpo_price[$pre_grn_wp_res->po_number][$pre_grn_wp_res->inventory_id];
                    }
                }
                //pr($pre_grn_wpo_arr); die;
                //===GRN WPO====//
                //===GRN WOPO====//

                $pre_grn_wopo_arr           =   array();
                $pre_grn_wopo_price_arr     =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
                if($pre_qry_grn_wop->num_rows()){
                    foreach($pre_qry_grn_wop->result() as $pre_grn_wop_res){
                        if(isset($pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]+$pre_grn_wop_res->grn_qty;
                        }
                        else{
                            $pre_grn_wopo_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wop_res->grn_qty;
                        }
                        if(isset($pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id])){
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]+(($pre_grn_wop_res->grn_qty)*($pre_grn_wop_res->rate));
                        }
                        else{
                            $pre_grn_wopo_price_arr[$pre_grn_wop_res->inventory_id]    =   ($pre_grn_wop_res->grn_qty)*($pre_grn_wop_res->rate);
                        }
                        $pre_grn_price_arr[$pre_grn_wop_res->id] =   $pre_grn_wop_res->rate;
                    }
                }
                //pr($pre_grn_wopo_arr); die;
                //===GRN WOPO====//
                //===Stock GRN===//
                $pre_grn_stock_arr =   array();
                $pre_grn_stock_price_arr =   array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_grn_stock = $this->db->select('id,grn_qty,inventory_id,stock_return_for')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
                if($pre_qry_grn_stock->num_rows()){
                    foreach($pre_qry_grn_stock->result() as $pre_grn_stock){
                        if(isset($pre_grn_stock_arr[$pre_grn_stock->inventory_id])){
                            $pre_grn_stock_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock_arr[$pre_grn_stock->inventory_id]+$pre_grn_stock->grn_qty;
                        }
                        else{
                            $pre_grn_stock_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock->grn_qty;
                        }
                        if(isset($pre_grn_stock_price_arr[$pre_grn_stock->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_grn_stock->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_grn_stock->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_grn_stock->stock_return_for];
                            }
                            $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]    =   $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]+($pre_grn_stock->grn_qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_grn_stock->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_grn_stock->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_grn_stock->stock_return_for];
                            }
                            $pre_grn_stock_price_arr[$pre_grn_stock->inventory_id]    =   $grn_stock->grn_qty*$os_grn_price;
                        }
                    }
                }
                //===Stock GRN===//
                //===Issued===//
                $pre_issued_arr = array();
                $pre_issued_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                //$this->db->group_by('inventory_id');
                //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
                $pre_qry_issued = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_mgt',array('is_deleted' => '0'));
                if($pre_qry_issued->num_rows()){
                    foreach($pre_qry_issued->result() as $pre_issue_res){
                        if(isset($pre_issued_arr[$pre_issue_res->inventory_id])){
                            $pre_issued_arr[$pre_issue_res->inventory_id]    =   $pre_issued_arr[$pre_issue_res->inventory_id]+$pre_issue_res->qty;
                        }
                        else{
                            $pre_issued_arr[$pre_issue_res->inventory_id]    =   $pre_issue_res->qty;
                        }
                        if(isset($pre_issued_price_arr[$pre_issue_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_issue_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_res->issued_return_for];
                            }
                            $pre_issued_price_arr[$pre_issue_res->inventory_id]    =   ($pre_issued_price_arr[$pre_issue_res->inventory_id])+($pre_issue_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_issue_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_res->issued_return_for];
                            }
                            $pre_issued_price_arr[$pre_issue_res->inventory_id]    =   $pre_issue_res->qty*$pre_os_grn_price;
                        }
                    }
                }
                //===Issued===//
                //====Issued Return===//
                $pre_issued_return_arr = array();
                $pre_issued_return_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                $pre_qry_issued_return = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_return_mgt',array('is_deleted' => '0'));
                if($pre_qry_issued_return->num_rows()){

                    foreach($pre_qry_issued_return->result() as $pre_issue_ret_res){
                        if(isset($pre_issued_return_arr[$pre_issue_ret_res->inventory_id])){
                            $pre_issued_return_arr[$pre_issue_ret_res->inventory_id]    =   ($pre_issued_return_arr[$pre_issue_ret_res->inventory_id])+($pre_issue_ret_res->qty);
                        }
                        else{
                            $pre_issued_return_arr[$pre_issue_ret_res->inventory_id]    =   $pre_issue_ret_res->qty;
                        }
                        if(isset($pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_issue_ret_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_ret_res->issued_return_for];
                            }
                            $pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id]    =   ($pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id])+($pre_issue_ret_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_issue_ret_res->issued_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_issue_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_issue_ret_res->issued_return_for];
                            }
                            $pre_issued_return_price_arr[$pre_issue_ret_res->inventory_id]    =   $pre_issue_ret_res->qty*$pre_os_grn_price;
                        }

                    }
                }
                // pr($issued_return_arr);
                // pr($issued_return_price_arr);die;
                //====Issued Return===//
                //===Stock Return=====//
                $pre_stock_return_arr = array();
                $pre_stock_return_price_arr = array();
                $this->db->where_in('inventory_id',$invarrs);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('last_updated_date<',$new_from_date);
                }
                //$this->db->group_by('inventory_id');
                //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
                $pre_qry_stock_return = $this->db->select('qty,inventory_id,stock_return_for')->get_where('tbl_return_stock',array('is_deleted' => '0'));
                if($pre_qry_stock_return->num_rows()){
                    foreach($pre_qry_stock_return->result() as $pre_stock_ret_res){
                        if(isset($pre_stock_return_arr[$pre_stock_ret_res->inventory_id])){
                            $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]+$pre_stock_ret_res->qty;
                        }
                        else{
                            $pre_stock_return_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_ret_res->qty;
                        }
                        if(isset($pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id])){
                            $pre_os_grn_price   =   0;
                            if($pre_stock_ret_res->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_stock_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_stock_ret_res->stock_return_for];
                            }
                            $pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id]    =   ($pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id])+($pre_stock_ret_res->qty*$pre_os_grn_price);
                        }
                        else{
                            $pre_os_grn_price   =   0;
                            if($pre_stock_ret_res->stock_return_for==0){
                                $pre_os_grn_price = $pre_grn_price_arr['os'][$pre_stock_ret_res->inventory_id];
                            }
                            else{
                                $pre_os_grn_price   =   $pre_grn_price_arr[$pre_stock_ret_res->stock_return_for];
                            }
                            $pre_stock_return_price_arr[$pre_stock_ret_res->inventory_id]    =   $pre_stock_ret_res->qty*$pre_os_grn_price;
                        }
                    }
                }
                //===Stock Return=====//
                //====Pre Cureent Stock===//
            }
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('updated_at>=',$new_from_date);
                $this->db->where('updated_at<=',$new_to_date);
            }
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){
                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('updated_at>=',$new_from_date);
                $this->db->where('updated_at<=',$new_to_date);
            }
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();
            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_date>=',$new_from_date);
                    $this->db->where('updated_date<=',$new_to_date);
                }
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at>=',$new_from_date);
                    $this->db->where('updated_at<=',$new_to_date);
                }
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//

            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_date>=',$new_from_date);
                    $this->db->where('updated_date<=',$new_to_date);
                }
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                if(isset($new_from_date) && isset($new_to_date)){
                    $this->db->where('updated_at>=',$new_from_date);
                    $this->db->where('updated_at<=',$new_to_date);
                }
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //====Wpo price===//
            $wpo_price = array();
            $this->db->where_in('inventory_id',$invarrs);
            $qry_rfq_price = $this->db->get_where('all_rfq_price_order',array('order_price !=' => ''));
            if($qry_rfq_price->num_rows()){
                foreach($qry_rfq_price->result() as $rp_row){
                    $wpo_price[$rp_row->po_number][$rp_row->inventory_id] = $rp_row->order_price;
                }
            }
            //====wpo price===//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            $grn_wpo_arr        =   array();
            $grn_wpo_price_arr  =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            $qry_grn_wp = $this->db->select('id,grn_qty,inventory_id,po_number,grn_buyer_rate')
            ->where_in('grn_type', array('1', '4'))
            ->get_where('grn_mgt',array(
                // 'grn_type' => '1',
                 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    if(isset($grn_wpo_arr[$grn_wp_res->inventory_id])){
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty;
                    }
                    else{
                        $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty;
                    }
                    if(isset($grn_wpo_price_arr[$grn_wp_res->inventory_id])){
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =  $grn_wpo_price_arr[$grn_wp_res->inventory_id] + $grn_wp_res->grn_qty*$per_price;
                    }
                    else{
                        $per_price = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                        if($grn_wp_res->grn_buyer_rate>0){
                            $per_price = $grn_wp_res->grn_buyer_rate;
                        }
                        $grn_wpo_price_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->grn_qty*$per_price;
                    }
                    $per_price_new = $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    if($grn_wp_res->grn_buyer_rate>0){
                        $per_price_new = $grn_wp_res->grn_buyer_rate;
                    }
                    //$grn_price_arr[$grn_wp_res->id] =   $wpo_price[$grn_wp_res->po_number][$grn_wp_res->inventory_id];
                    $grn_price_arr[$grn_wp_res->id] =   $per_price_new;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//

            $grn_wopo_arr           =   array();
            $grn_wopo_price_arr     =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '2', 'is_deleted' => '0'));
            $qry_grn_wop = $this->db->select('id,grn_qty,inventory_id,rate')->get_where('grn_mgt',array('grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    if(isset($grn_wopo_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_arr[$grn_wop_res->inventory_id]+$grn_wop_res->grn_qty;
                    }
                    else{
                        $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->grn_qty;
                    }
                    if(isset($grn_wopo_price_arr[$grn_wop_res->inventory_id])){
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   $grn_wopo_price_arr[$grn_wop_res->inventory_id]+(($grn_wop_res->grn_qty)*($grn_wop_res->rate));
                    }
                    else{
                        $grn_wopo_price_arr[$grn_wop_res->inventory_id]    =   ($grn_wop_res->grn_qty)*($grn_wop_res->rate);
                    }
                    $grn_price_arr[$grn_wop_res->id] =   $grn_wop_res->rate;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $grn_stock_price_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '3', 'is_deleted' => '0'));
            $qry_grn_stock = $this->db->select('id,grn_qty,inventory_id,stock_return_for')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    if(isset($grn_stock_arr[$grn_stock->inventory_id])){
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock_arr[$grn_stock->inventory_id]+$grn_stock->grn_qty;
                    }
                    else{
                        $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty;
                    }
                    if(isset($grn_stock_price_arr[$grn_stock->inventory_id])){
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$grn_stock->stock_return_for];
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock_price_arr[$grn_stock->inventory_id]+($grn_stock->grn_qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($grn_stock->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$grn_stock->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$grn_stock->stock_return_for];
                        }
                        $grn_stock_price_arr[$grn_stock->inventory_id]    =   $grn_stock->grn_qty*$os_grn_price;
                    }
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $issued_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            $qry_issued = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    if(isset($issued_arr[$issue_res->inventory_id])){
                        $issued_arr[$issue_res->inventory_id]    =   $issued_arr[$issue_res->inventory_id]+$issue_res->qty;
                    }
                    else{
                        $issued_arr[$issue_res->inventory_id]    =   $issue_res->qty;
                    }
                    if(isset($issued_price_arr[$issue_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_res->issued_return_for];
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   ($issued_price_arr[$issue_res->inventory_id])+($issue_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_res->issued_return_for];
                        }
                        $issued_price_arr[$issue_res->inventory_id]    =   $issue_res->qty*$os_grn_price;
                    }
                }
            }
            //===Issued===//
            // pr($issued_price_arr);die;
            //====Issued Return===//
            $issued_return_arr = array();
            $issued_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            $qry_issued_return = $this->db->select('qty,inventory_id,issued_return_for')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    if(isset($issued_return_arr[$issue_ret_res->inventory_id])){
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   ($issued_return_arr[$issue_ret_res->inventory_id])+($issue_ret_res->qty);
                    }
                    else{
                        $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->qty;
                    }
                    if(isset($issued_return_price_arr[$issue_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_ret_res->inventory_id]    =   ($issued_return_price_arr[$issue_ret_res->inventory_id])+($issue_ret_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($issue_ret_res->issued_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$issue_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$issue_ret_res->issued_return_for];
                        }
                        $issued_return_price_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->qty*$os_grn_price;
                    }
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $stock_return_price_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            if(isset($new_from_date) && isset($new_to_date)){
                $this->db->where('last_updated_date>=',$new_from_date);
                $this->db->where('last_updated_date<=',$new_to_date);
            }
            //$this->db->group_by('inventory_id');
            //$qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            $qry_stock_return = $this->db->select('qty,inventory_id,stock_return_for')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    if(isset($stock_return_arr[$stock_ret_res->inventory_id])){
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_return_arr[$stock_ret_res->inventory_id]+$stock_ret_res->qty;
                    }
                    else{
                        $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty;
                    }
                    if(isset($stock_return_price_arr[$stock_ret_res->inventory_id])){
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    =   ($stock_return_price_arr[$stock_ret_res->inventory_id])+($stock_ret_res->qty*$os_grn_price);
                    }
                    else{
                        $os_grn_price   =   0;
                        if($stock_ret_res->stock_return_for==0){
                            $os_grn_price = $grn_price_arr['os'][$stock_ret_res->inventory_id];
                        }
                        else{
                            $os_grn_price   =   $grn_price_arr[$stock_ret_res->stock_return_for];
                        }
                        $stock_return_price_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->qty*$os_grn_price;
                    }
                }
            }
            //===Stock Return=====//
        }
        // pr($grn_stock_price_arr);die;
        $data1 = array();
        $final_data = array();
        $data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            //===Indent Qty==//
                //$total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_price = 0;
            if(isset($grn_wpo_price_arr[$val->id])){
                $grn_qty_price = $grn_wpo_price_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_wop_price = 0;
            if(isset($grn_wopo_price_arr[$val->id])){
                $grn_qty_wop_price = $grn_wopo_price_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }

            $grn_qty_stok_price = 0;
            if(isset($grn_stock_price_arr[$val->id])){
                $grn_qty_stok_price = $grn_stock_price_arr[$val->id];
            }

            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            $issued_qty_price = 0;
            if(isset($issued_price_arr[$val->id])){
                $issued_qty_price = $issued_price_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            $issued_return_qty_price = 0;
            if(isset($issued_return_price_arr[$val->id])){
                $issued_return_qty_price = $issued_return_price_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            $stock_return_qty_price = 0;
            if(isset($stock_return_price_arr[$val->id])){
                $stock_return_qty_price = $stock_return_price_arr[$val->id];
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $opening_stock_price = $val->opening_stock*$val->stock_price;
            $mystock            =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock_price      =   ($opening_stock_price+$grn_qty_price+$grn_qty_wop_price+$grn_qty_stok_price+$issued_return_qty_price)-($issued_qty_price+$stock_return_qty_price);
            if(isset($new_from_date) && isset($new_to_date)){
                //===pre GRN====//
                $pre_grn_qty = 0;
                if(isset($pre_grn_wpo_arr[$val->id])){
                    $pre_grn_qty = $pre_grn_wpo_arr[$val->id];
                }
                $pre_grn_qty_price = 0;
                if(isset($pre_grn_wpo_price_arr[$val->id])){
                    $pre_grn_qty_price = $pre_grn_wpo_price_arr[$val->id];
                }
                $pre_grn_qty_wop = 0;
                if(isset($pre_grn_wopo_arr[$val->id])){
                    $pre_grn_qty_wop = $pre_grn_wopo_arr[$val->id];
                }
                $pre_grn_qty_wop_price = 0;
                if(isset($pre_grn_wopo_price_arr[$val->id])){
                    $pre_grn_qty_wop_price = $pre_grn_wopo_price_arr[$val->id];
                }
                $pre_grn_qty_stok = 0;
                if(isset($pre_grn_stock_arr[$val->id])){
                    $pre_grn_qty_stok = $pre_grn_stock_arr[$val->id];
                }

                $pre_grn_qty_stok_price = 0;
                if(isset($pre_grn_stock_price_arr[$val->id])){
                    $pre_grn_qty_stok_price = $pre_grn_stock_price_arr[$val->id];
                }

                //====GRN====//
                //===Issued=====//
                $pre_issued_qty = 0;
                if(isset($pre_issued_arr[$val->id])){
                    $pre_issued_qty = $pre_issued_arr[$val->id];
                }
                $pre_issued_qty_price = 0;
                if(isset($pre_issued_price_arr[$val->id])){
                    $pre_issued_qty_price = $pre_issued_price_arr[$val->id];
                }
                //===Issued=====//
                //===Isseued Return==//
                $pre_issued_return_qty = 0;
                if(isset($pre_issued_arr[$val->id])){
                    $pre_issued_return_qty = $pre_issued_return_arr[$val->id];
                }
                $pre_issued_return_qty_price = 0;
                if(isset($pre_issued_return_price_arr[$val->id])){
                    $pre_issued_return_qty_price = $pre_issued_return_price_arr[$val->id];
                }
                //===Issued Return===//
                //===Stock Return===//
                $pre_stock_return_qty = 0;
                if(isset($pre_stock_return_arr[$val->id])){
                    $pre_stock_return_qty = $pre_stock_return_arr[$val->id];
                }
                $pre_stock_return_qty_price = 0;
                if(isset($pre_stock_return_price_arr[$val->id])){
                    $pre_stock_return_qty_price = $pre_stock_return_price_arr[$val->id];
                }
                //===Stock Return====//
                $mystock    =   ($mystock+$pre_grn_qty+$pre_grn_qty_wop+$pre_grn_qty_stok+$pre_issued_return_qty)-($pre_issued_qty+$pre_stock_return_qty);
                $mystock_price      =   ($mystock_price+$pre_grn_qty_price+$pre_grn_qty_wop_price+$pre_grn_qty_stok_price+$pre_issued_return_qty_price)-($pre_issued_qty_price+$pre_stock_return_qty_price);
            }

            $final_data[$i]['Product']              =   $val->prod_name;
            $final_data[$i]['Our Product Name']     =   $val->buyer_product_name;
            $final_data[$i]['Specification']        =   $val->specification;
            $final_data[$i]['Size']                 =   $val->size;
            $final_data[$i]['grp']                  =   $val->inventory_grouping;
            $final_data[$i]['UOM']                  =   $val->uom_name;
            $final_data[$i]['current_stock_qty']    =   round($mystock,2);
            // $final_data[$i]['total_amount']         =   formatIndianRupees($mystock_price);
            $formatted_price = formatIndianRupees($mystock_price);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
            $final_data[$i]['total_amount']=$formatted_price;

            if($mystock>0 || $issued_qty>0){
                 $orgnal_issued_qty = $issued_qty-$issued_return_qty;
                $sub_array[] = $orgnal_issued_qty;
                $final_data[$i]['issued_qty']       =   round($orgnal_issued_qty,2);
            }
            else{
                $final_data[$i]['issued_qty']       =   0;
            }
            if($mystock>0 || $issued_qty>0){
                $orgnal_issued_qty_price = $issued_qty_price-$issued_return_qty_price;
                // $final_data[$i]['Issued_Amount'] = formatIndianRupees($orgnal_issued_qty_price);
                $formatted_price = formatIndianRupees($orgnal_issued_qty_price);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                $final_data[$i]['Issued_Amount']=$formatted_price;
            }
            else{
                 $final_data[$i]['Issued_Amount'] = '0';
            }
            // $final_data[$i]['Issued_Amount']        =   isset($issued_inven_price[$val->id]) ? $issued_inven_price[$val->id] : 0;
            if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok>0){
                $final_GRN_Qty              =   $grn_qty+$grn_qty_wop + $grn_qty_stok;
                $final_data[$i]['GRN_Qty']  =   round($final_GRN_Qty,2);
            }
            else{
               $final_data[$i]['GRN_Qty'] = 0;
            }
            // $final_data[$i]['GRN_Qty']              =   isset($grn_qty) ? $grn_qty : 0;
            if($grn_qty>0 || $grn_qty_wop>0 || $grn_qty_stok_price>0){
               $final_GRN_Amount = $grn_qty_price + $grn_qty_wop_price + $grn_qty_stok_price;
            //    $final_data[$i]['GRN_Amount'] = formatIndianRupees($final_GRN_Amount);
            $formatted_price = formatIndianRupees($final_GRN_Amount);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
            $final_data[$i]['GRN_Amount']=$formatted_price;
            }
            else{
                $final_data[$i]['GRN_Amount'] = 0;
            }
            // $final_data[$i]['GRN_Amount']           =   isset($grn_inv_price[$val->id]) ? $grn_inv_price[$val->id] : 0;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function export_get_stock_return_report(){
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_stock_return_report_data($users_ids, $buyer_users,'page',$cat_id);
        $final_data =   array();
        $i          =   0;
        foreach ($result as $key => $val) {
            $sub_array = array();
            $final_data[$i]['stock Number']         =   $val->stock_no;
            $final_data[$i]['Product']              =   $val->prod_name;
            $final_data[$i]['Specification']        =   $val->specification;
            $final_data[$i]['Size']                 =   $val->size;
            $final_data[$i]['grp']                  =   $val->inventory_grouping;
            $final_data[$i]['styp']                 =   $val->stock_type;
            $final_data[$i]['Added BY']             =   $val->first_name; //$val->first_name.' '.$val->last_name;
            $final_data[$i]['Vendor name']          =   $val->stock_vendor_name;
            $final_data[$i]['Added Date']           =   date("d/m/Y", strtotime($val->last_updated_date));
            if($val->is_deleted==0){
                $final_data[$i]['Issued Quantity']  =   round($val->qty,2);
            }
            else{
                $final_data[$i]['Issued Quantity']  =   round($val->qty,2).'(Deleted)';
            }
            $final_data[$i]['UOM']                  =   $val->uom_name;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function export_get_issued_return_report()
    {
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_issued_return_report_data($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_issued_return_report_data($users_ids, $buyer_users, 'total',$cat_id);
        //pr($result); die;
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $div_arrs = array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',', $vals->category_ids);
            $div_arrs[$dcat_arr['0']] = $dcat_arr['0'];
            $div_arrs[$dcat_arr['1']] = $dcat_arr['1'];
        }
        $issued_return_type = array();
        $qrys = $this->db->get('issued_type');
        if($qrys->num_rows() > 0){
            foreach($qrys->result() as $v){
                $issued_return_type[$v->id] = $v->name;
            }
        }
        $final_data = array();
        // $i = 0;
        $i = 1;
        foreach ($result as $key => $val) {

            $sub_array = array();
            //listing------
            $final_data[$i]['Stock No']         =   $val->issued_return_no;
            $final_data[$i]['Product']          =   $val->prod_name;
            $final_data[$i]['Specification']    =   ($val->specification);
            $final_data[$i]['Size']             =   ($val->size);
            $final_data[$i]['grp']              =   ($val->inventory_grouping);
            $final_data[$i]['irt']              =   $issued_return_type[$val->issued_return_type];
            $final_data[$i]['Added BY']         =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date']       =   date("d/m/Y", strtotime($val->last_updated_date));
            if ($val->is_deleted == 0) {
                $final_data[$i]['Issued Quantity'] = round($val->qty,2);
            } else {
                $final_data[$i]['Issued Quantity'] = round($val->qty,2) . '(Deleted)';
            }
            $final_data[$i]['UOM']          =   $val->uom_name;
            $final_data[$i]['Remarks']      =   $val->remark;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function get_grn_report_data()
    {

        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id    =   $this->session->userdata('auth_user')['users_id'];
        $users      =   $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record   =   $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $po_numbers         =   array();
        $po_by_id           =   array();
        $manual_po_numbers  =   array();
        $manual_po_by_id    =   array();
        $price_by_po        =   array();
        $get_grn_type       =   array();
        $with_po_price      =   array();
        $vendor_detail      =   array();
        $invarrs            =   array();
        $grn_buyer_rate     =   array();
        $new_stock_return_for = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $invarrs[$v->inventory_id]=$v->inventory_id;
                if($v->grn_type == 1){
                    $po_numbers[]           =   $v->po_number;
                    $po_by_id[$v->id]       =   $v->po_number;
                    if($v->grn_buyer_rate>0){
                        $grn_buyer_rate[$v->id]         =   $v->grn_buyer_rate;
                        $grn_buyer_rate[$v->po_number]  =   $v->grn_buyer_rate;
                    }

                }elseif($v->grn_type == 4){
                    $manual_po_numbers[]       =   $v->po_number;
                    $manual_po_by_id[$v->id]   =   $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id]  =   $v->rate;
                }
                elseif($v->grn_type == 3){
                    if($v->stock_return_for != 0){
                        $new_stock_return_for[$v->stock_return_for] = $v->stock_return_for;
                    }
                }
                $get_grn_type[$v->id]   =   $v->grn_type;
            }
            if(isset($new_stock_return_for) && !empty($new_stock_return_for)){
                $this->db->where_in('id',$new_stock_return_for);
                $new_st_grn_qry = $this->db->select('id,grn_type,po_number,rate')->get_where('grn_mgt',array());
                if($new_st_grn_qry->num_rows()){
                    foreach($new_st_grn_qry->result() as $stgrnqry){
                        if($stgrnqry->grn_type == 1){
                            if(!in_array($stgrnqry->po_number,$po_numbers)){
                                $po_numbers[]               =   $stgrnqry->po_number;
                            }
                            $po_by_id[$stgrnqry->id]    =   $stgrnqry->po_number;

                        }
                        elseif($stgrnqry->grn_type == 4 ){
                            if(!in_array($stgrnqry->po_number,$manual_po_numbers)){
                            $manual_po_numbers[] = $stgrnqry->po_number;
                            }
                            $manual_po_by_id[$stgrnqry->id] = $stgrnqry->po_number;
                        }
                        elseif($stgrnqry->grn_type == 2){
                            $with_po_price[$stgrnqry->id]  =   $stgrnqry->rate;
                        }
                        $get_grn_type[$stgrnqry->id]   =   $stgrnqry->grn_type;
                    }
                }
            }
            ///pr($po_by_id); die;
            if(isset($po_numbers) && !empty($po_numbers)){
                $this->db->where_in('po_number',$po_numbers);
                //$this->db->where_in('tr.inventory_id',$invarrs);
                //$qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
                $this->db->select("tro.id,tro.price_id,tro.po_number,tro.order_quantity,tro.updated_at,tro.order_price,tr.vend_id,ts.store_name,tr.inventory_id", false);
                $this->db->from("tbl_rfq_order as tro");
                $this->db->join("tbl_rfq_price as trp", 'trp.id=tro.price_id', 'LEFT');
                $this->db->join("tbl_rfq as tr", 'tr.id=trp.rfq_record_id', 'LEFT');
                $this->db->join("tbl_store as ts",'ts.store_id=tr.vend_id');
                $qry_rfq_placeorder = $this->db->get();
                //pr($qry_rfq_placeorder->result()); die;
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
            if(isset($manual_po_numbers) && !empty($manual_po_numbers)){
                $this->db->where_in('manual_po_number',$manual_po_numbers);
                $this->db->select("mpo.id,mpo.manual_po_number as po_number,mpo.product_quantity as order_quantity,mpo.updated_at,mpo.product_price as order_price,mpo.vendor_id as vend_id,ts.store_name,mpo.inventory_id", false);

                $this->db->from("tbl_manual_po_order as mpo");
                $this->db->join("tbl_store as ts",'ts.store_id=mpo.vendor_id ');
                $qry_manual_placeorder = $this->db->get();
                if($qry_manual_placeorder->num_rows()){
                    foreach($qry_manual_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
        }
        //pr($price_by_po); die;
        //pr($vendor_detail);
        // pr($price_by_po[$po_by_id[1]]);die;

        $totindqty = array();
        $no_inven_data = [];

        $sr_no = 1;
        $data1 = array();
        //pr($result); die;
        foreach ($result as $key => $val) {
            $sub_array = [];

            $sub_array[] = $val->grn_no;
            $sub_array[] = $val->prod_name;
            $sub_array[] = strlen($val->specification) <= 20 ? $val->specification : substr($val->specification, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->specification . '"></i>';
            $sub_array[] = strlen($val->size) <= 20 ? $val->size : substr($val->size, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->size . '"></i>';
            $sub_array[] = strlen($val->inventory_grouping) <= 20 ? $val->inventory_grouping : substr($val->inventory_grouping, 0, 20) . '<i class="bi bi-info-circle-fill" title="' . $val->inventory_grouping . '"></i>';

            $sub_array[] = ($val->grn_type == 1 || $val->grn_type == 4) ? ($vendor_detail[$val->po_number][$val->inventory_id] ?? '') : $val->vendor_name;

            $sub_array = array_merge($sub_array, [
                $val->vendor_invoice_number,
                $val->vehicle_no_lr_no,
                $val->gross_wt,
                $val->gst_no,
                $val->frieght_other_charges,
                $val->first_name,
                date("d/m/Y", strtotime($val->last_updated_date)),
                '<span class="grn_qtys" id="inven_grn_qtys_' . $val->id . '" onclick="show_edit_grn_model(' . $val->id . ')">' . round($val->grn_qty, 2) . '</span>',
                $val->uom_name
            ]);

            $grn_price = 0;
            if ($val->grn_type == 1 || $val->grn_type == 4) {
                $get_per_price = $grn_buyer_rate[$val->po_number] ?? $price_by_po[$val->po_number][$val->inventory_id];
                $grn_price = round($get_per_price, 2) * $val->grn_qty;
            } elseif ($val->grn_type == 2) {
                $grn_price = round($val->rate, 2) * $val->grn_qty;
            } elseif ($val->stock_return_for > 0) {
                $stock_return_type = $get_grn_type[$val->stock_return_for] ?? null;
                if ($stock_return_type == 1 || $stock_return_type == 4) {
                    $po_id = $stock_return_type == 1 ? $po_by_id[$val->stock_return_for] : $manual_po_by_id[$val->stock_return_for];
                    $get_per_price = $grn_buyer_rate[$po_id] ?? $price_by_po[$po_id][$val->inventory_id];
                    $grn_price = round($get_per_price, 2) * $val->grn_qty;
                } elseif ($stock_return_type == 2) {
                    $grn_price = round($with_po_price[$val->stock_return_for], 2) * $val->grn_qty;
                }
            } else {
                $grn_price = round($val->stock_price, 2) * $val->grn_qty;
            }

            $formatted_price = $grn_price > 1 ? formatIndianRupees($grn_price) : ($grn_price >= 0.01 ? $grn_price : '0.00');
            $formatted_price .= strpos($formatted_price, '.') === false ? '.00' : '';

            $sub_array[] = $formatted_price;
            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }
    public function export_grn_report()
    {
        $cat_id = array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id    =   $this->session->userdata('auth_user')['users_id'];
        $users      =   $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record   =   $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $po_numbers         =   array();
        $po_by_id           =   array();
        $manual_po_numbers  =   array();
        $manual_po_by_id    =   array();
        $price_by_po        =   array();
        $get_grn_type       =   array();
        $with_po_price      =   array();
        $vendor_detail      =   array();
        $invarrs            =   array();
        $grn_buyer_rate     =   array();
        $new_stock_return_for = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $invarrs[$v->inventory_id]=$v->inventory_id;
                if($v->grn_type == 1){
                    $po_numbers[]           =   $v->po_number;
                    $po_by_id[$v->id]       =   $v->po_number;
                    if($v->grn_buyer_rate>0){
                        $grn_buyer_rate[$v->id]         =   $v->grn_buyer_rate;
                        $grn_buyer_rate[$v->po_number]  =   $v->grn_buyer_rate;
                    }

                }elseif($v->grn_type == 4){
                    $manual_po_numbers[]       =   $v->po_number;
                    $manual_po_by_id[$v->id]   =   $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id]  =   $v->rate;
                }
                elseif($v->grn_type == 3){
                    if($v->stock_return_for != 0){
                        $new_stock_return_for[$v->stock_return_for] = $v->stock_return_for;
                    }
                }
                $get_grn_type[$v->id]   =   $v->grn_type;
            }
            if(isset($new_stock_return_for) && !empty($new_stock_return_for)){
                $this->db->where_in('id',$new_stock_return_for);
                $new_st_grn_qry = $this->db->select('id,grn_type,po_number,rate')->get_where('grn_mgt',array());
                if($new_st_grn_qry->num_rows()){
                    foreach($new_st_grn_qry->result() as $stgrnqry){
                        if($stgrnqry->grn_type == 1){
                            if(!in_array($stgrnqry->po_number,$po_numbers)){
                                $po_numbers[]               =   $stgrnqry->po_number;
                            }
                            $po_by_id[$stgrnqry->id]    =   $stgrnqry->po_number;

                        }
                        elseif($stgrnqry->grn_type == 4 ){
                            if(!in_array($stgrnqry->po_number,$manual_po_numbers)){
                            $manual_po_numbers[] = $stgrnqry->po_number;
                            }
                            $manual_po_by_id[$stgrnqry->id] = $stgrnqry->po_number;
                        }
                        elseif($stgrnqry->grn_type == 2){
                            $with_po_price[$stgrnqry->id]  =   $stgrnqry->rate;
                        }
                        $get_grn_type[$stgrnqry->id]   =   $stgrnqry->grn_type;
                    }
                }
            }
            ///pr($po_by_id); die;
            if(isset($po_numbers) && !empty($po_numbers)){
                $this->db->where_in('po_number',$po_numbers);
                //$this->db->where_in('tr.inventory_id',$invarrs);
                //$qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
                $this->db->select("tro.id,tro.price_id,tro.po_number,tro.order_quantity,tro.updated_at,tro.order_price,tr.vend_id,ts.store_name,tr.inventory_id", false);
                $this->db->from("tbl_rfq_order as tro");
                $this->db->join("tbl_rfq_price as trp", 'trp.id=tro.price_id', 'LEFT');
                $this->db->join("tbl_rfq as tr", 'tr.id=trp.rfq_record_id', 'LEFT');
                $this->db->join("tbl_store as ts",'ts.store_id=tr.vend_id');
                $qry_rfq_placeorder = $this->db->get();
                //pr($qry_rfq_placeorder->result()); die;
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
            if(isset($manual_po_numbers) && !empty($manual_po_numbers)){
                $this->db->where_in('manual_po_number',$manual_po_numbers);
                $this->db->select("mpo.id,mpo.manual_po_number as po_number,mpo.product_quantity as order_quantity,mpo.updated_at,mpo.product_price as order_price,mpo.vendor_id as vend_id,ts.store_name,mpo.inventory_id", false);

                $this->db->from("tbl_manual_po_order as mpo");
                $this->db->join("tbl_store as ts",'ts.store_id=mpo.vendor_id ');
                $qry_manual_placeorder = $this->db->get();
                if($qry_manual_placeorder->num_rows()){
                    foreach($qry_manual_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
        }
        //pr($price_by_po); die;
        //pr($vendor_detail);
        // pr($price_by_po[$po_by_id[1]]);die;

        $totindqty      =   array();
        $no_inven_data  =   [];

        $sr_no          =   1;
        $data1          =   array();
        $final_data     =   array();
        $i              =   0;
        //pr($result); die;
        foreach ($result as $key => $val) {
            $final_data[$i]['GRN Number']       =   $val->grn_no;
            $final_data[$i]['Product']          =   $val->prod_name;
            $final_data[$i]['Specification']    =   ($val->specification);
            $final_data[$i]['Size']             =   ($val->size);
            $final_data[$i]['grp']              =   ($val->inventory_grouping);
            if($val->grn_type==1|| $val->grn_type==4){
                $final_data[$i]['v_name']       =   isset($vendor_detail[$val->po_number][$val->inventory_id]) ? $vendor_detail[$val->po_number][$val->inventory_id] : '';
            }
            else{
                $final_data[$i]['v_name']                   =   HtmlDecodeString($val->vendor_name);
            }
            $final_data[$i]['Vendor Invoice No']            =   HtmlDecodeString($val->vendor_invoice_number);
            $final_data[$i]['Vehicle No/ LR No']            =   HtmlDecodeString($val->vehicle_no_lr_no);
            $final_data[$i]['Gross Wt (kgs)']               =   HtmlDecodeString($val->gross_wt);
            $final_data[$i]['GST No.']                      =   $val->gst_no;
            $final_data[$i]['Frieght / Other Charges(₹)']   =   HtmlDecodeString($val->frieght_other_charges);
            $final_data[$i]['Added BY']                     =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date']                   =   date("d/m/Y", strtotime($val->last_updated_date));
            if ($val->is_deleted == 0) {
                $final_data[$i]['GRN Quantity'] = round($val->grn_qty,2);
            }else{
                 $final_data[$i]['GRN Quantity'] = round($val->grn_qty,2) . ' (Deleted)';
            }
            $final_data[$i]['UOM'] = $val->uom_name;
            if($val->grn_type ==1|| $val->grn_type==4){
                $get_per_prices             =   isset($grn_buyer_rate[$val->po_number]) ? $grn_buyer_rate[$val->po_number] :  $price_by_po[$val->po_number][$val->inventory_id];
                $grn_prices                 =   round($get_per_prices,2) * $val->grn_qty;
                // $final_data[$i]['amounts']  =   formatIndianRupees($grn_prices);
                if($grn_prices>'1'){
                    $formatted_price = formatIndianRupees($grn_prices);
                }
                else{
                    $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['amounts']=$formatted_price;
            }elseif($val->grn_type ==2){
                $grn_prices                 =   round($val->rate,2) * $val->grn_qty;
                // $final_data[$i]['amounts']  =   formatIndianRupees($grn_prices);
                if($grn_prices>'1'){
                    $formatted_price = formatIndianRupees($grn_prices);
                }
                else{
                    $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['amounts']=$formatted_price;
            }else{
                if($val->stock_return_for > '0'){
                    if($get_grn_type[$val->stock_return_for] == 1){
                        $get_per_prices             =   isset($grn_buyer_rate[$po_by_id[$val->stock_return_for]]) ? $grn_buyer_rate[$po_by_id[$val->stock_return_for]] :  $price_by_po[$po_by_id[$val->stock_return_for]][$val->inventory_id];
                        $grn_prices                 =   round($get_per_prices,2) * $val->grn_qty;
                        // $final_data[$i]['amounts']  =   formatIndianRupees($grn_prices);
                        if($grn_prices>'1'){
                            $formatted_price = formatIndianRupees($grn_prices);
                        }
                        else{
                            $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                        }
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }
                    elseif($get_grn_type[$val->stock_return_for] == 4){

                        $get_per_prices                 =   isset($grn_buyer_rate[$manual_po_by_id[$val->stock_return_for]]) ? $grn_buyer_rate[$manual_po_by_id[$val->stock_return_for]] :  $price_by_po[$manual_po_by_id[$val->stock_return_for]][$val->inventory_id];
                        $grn_prices                     =   round($get_per_prices,2) * $val->grn_qty;
                        // $final_data[$i]['amounts']      =   formatIndianRupees($grn_prices);
                        if($grn_prices>'1'){
                            $formatted_price = formatIndianRupees($grn_prices);
                        }
                        else{
                            $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                        }
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }
                    elseif($get_grn_type[$val->stock_return_for] == 2){
                        $grn_prices                     =   round($with_po_price[$val->stock_return_for],2) * $val->grn_qty;
                        // $final_data[$i]['amounts']      =   formatIndianRupees($grn_prices);
                        if($grn_prices>'1'){
                            $formatted_price = formatIndianRupees($grn_prices);
                        }
                        else{
                            $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                        }
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }
                }else{
                    $grn_prices                         =   round($val->stock_price,2) * $val->grn_qty;
                    // $final_data[$i]['amounts']          =   formatIndianRupees($grn_prices);
                    if($grn_prices>'1'){
                        $formatted_price = formatIndianRupees($grn_prices);
                    }
                    else{
                        $formatted_price = $grn_prices >= '.01' ? $grn_prices : '0.00';
                    }
                    if (strpos($formatted_price, '.') === false) {
                        $formatted_price .= '.00';
                    }
                    $final_data[$i]['amounts']=$formatted_price;
                }

            }
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }
    public function export_grn_report_09jan25()
    {
        $cat_id = array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_grn_report_data($users_ids, $buyer_users, 'total', $cat_id);
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $div_arrs = array();
        foreach ($result as $key => $vals) {
            $dcat_arr = explode(',', $vals->category_ids);
            $div_arrs[$dcat_arr['0']] = $dcat_arr['0'];
            $div_arrs[$dcat_arr['1']] = $dcat_arr['1'];
        }
        $po_numbers     =   array();
        $po_by_id       =   array();
        $manual_po_numbers     =   array();
        $manual_po_by_id       =   array();
        $price_by_po    =   array();
        $get_grn_type   =   array();
        $with_po_price  =   array();
        $vendor_detail  =   array();
        $invarrs            =   array();
        $grn_buyer_rate     =   array();
        $new_stock_return_for = array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $invarrs[$v->inventory_id]=$v->inventory_id;
                if($v->grn_type == 1){
                    $po_numbers[] = $v->po_number;
                    $po_by_id[$v->id] = $v->po_number;
                    if($v->grn_buyer_rate>0){
                        $grn_buyer_rate[$v->id]         =   $v->grn_buyer_rate;
                        $grn_buyer_rate[$v->po_number]  =   $v->grn_buyer_rate;
                    }

                }elseif($v->grn_type == 4){
                    $manual_po_numbers[]       =   $v->po_number;
                    $manual_po_by_id[$v->id]   =   $v->po_number;

                }elseif($v->grn_type == 2){
                    $with_po_price[$v->id] = $v->rate;
                }
                elseif($v->grn_type == 3){
                    if($v->stock_return_for != 0){
                        $new_stock_return_for[$v->stock_return_for] = $v->stock_return_for;
                    }
                }
                $get_grn_type[$v->id] = $v->grn_type;
            }
            if(isset($new_stock_return_for) && !empty($new_stock_return_for)){
                $this->db->where_in('id',$new_stock_return_for);
                $new_st_grn_qry = $this->db->select('id,grn_type,po_number,rate')->get_where('grn_mgt',array());
                if($new_st_grn_qry->num_rows()){
                    foreach($new_st_grn_qry->result() as $stgrnqry){
                        if($stgrnqry->grn_type == 1){
                            if(!in_array($stgrnqry->po_number,$po_numbers)){
                                $po_numbers[]               =   $stgrnqry->po_number;
                            }
                            $po_by_id[$stgrnqry->id]    =   $stgrnqry->po_number;

                        }
                        elseif($stgrnqry->grn_type == 4 ){
                            if(!in_array($stgrnqry->po_number,$manual_po_numbers)){
                            $manual_po_numbers[] = $stgrnqry->po_number;
                            }
                            $manual_po_by_id[$stgrnqry->id] = $stgrnqry->po_number;
                        }
                        elseif($stgrnqry->grn_type == 2){
                            $with_po_price[$stgrnqry->id]  =   $stgrnqry->rate;
                        }
                        $get_grn_type[$stgrnqry->id]   =   $stgrnqry->grn_type;
                    }
                }
            }
            if(isset($po_numbers) && !empty($po_numbers)){
                $this->db->where_in('po_number',$po_numbers);
                //$qry_rfq_placeorder = $this->db->select('id,price_id,po_number,order_quantity,updated_at,order_price')->get_where('tbl_rfq_order',array('order_status' => 1));
                $this->db->select("tro.id,tro.price_id,tro.po_number,tro.order_quantity,tro.updated_at,tro.order_price,tr.vend_id,tr.inventory_id,ts.store_name", false);
                $this->db->from("tbl_rfq_order as tro");
                $this->db->join("tbl_rfq_price as trp", 'trp.id=tro.price_id', 'LEFT');
                $this->db->join("tbl_rfq as tr", 'tr.id=trp.rfq_record_id', 'LEFT');
                $this->db->join("tbl_store as ts",'ts.store_id=tr.vend_id');
                $qry_rfq_placeorder = $this->db->get();
                //pr($qry_rfq_placeorder->result()); die;
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
            if(isset($manual_po_numbers) && !empty($manual_po_numbers)){
                $this->db->where_in('manual_po_number',$manual_po_numbers);
                $this->db->select("mpo.id,mpo.manual_po_number as po_number,mpo.product_quantity as order_quantity,mpo.updated_at,mpo.product_price as order_price,mpo.vendor_id as vend_id,ts.store_name,mpo.inventory_id", false);

                $this->db->from("tbl_manual_po_order as mpo");
                $this->db->join("tbl_store as ts",'ts.store_id=mpo.vendor_id ');
                $qry_manual_placeorder = $this->db->get();
                if($qry_manual_placeorder->num_rows()){
                    foreach($qry_manual_placeorder->result() as $po_n){
                        $price_by_po[$po_n->po_number][$po_n->inventory_id]      =    $po_n->order_price;
                        $vendor_detail[$po_n->po_number][$po_n->inventory_id]    =   $po_n->store_name;
                    }
                }
            }
        }
        $final_data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            // $expdivcat = explode(',',$val->category_ids);
            // $alldivcatnames = getCategorySubCategoryName_smt($div_arrs);
            // $finldivcat['division_name']=isset($alldivcatnames[$expdivcat['0']]) ? $alldivcatnames[$expdivcat['0']] : '';
            // $finldivcat['category_name']=isset($alldivcatnames[$expdivcat['1']]) ? $alldivcatnames[$expdivcat['1']] : '';
            $sub_array = array();
            //listing------
            $final_data[$i]['GRN Number'] = $val->grn_no;
            $final_data[$i]['Product'] = $val->prod_name;
            //$final_data[$i]['Division'] = $val->div_name;
            //$final_data[$i]['Category'] = $val->cat_name;
            $final_data[$i]['Specification'] = $val->specification;
            $final_data[$i]['Size'] = $val->size;
            $final_data[$i]['grp'] = $val->inventory_grouping;
            if($val->grn_type==1|| $val->grn_type==4){
                $final_data[$i]['v_name'] = isset($vendor_detail[$val->po_number][$val->inventory_id]) ? $vendor_detail[$val->po_number][$val->inventory_id] : '';
            }
            else{
                $final_data[$i]['v_name'] = $val->vendor_name;
            }
            $final_data[$i]['Vendor Invoice No'] = $val->vendor_invoice_number;
            $final_data[$i]['Vehicle No/ LR No'] = $val->vehicle_no_lr_no;
            $final_data[$i]['Gross Wt (kgs)'] = $val->gross_wt;
            $final_data[$i]['GST No.'] = $val->gst_no;
            $final_data[$i]['Frieght / Other Charges(₹)'] = $val->frieght_other_charges;
            $final_data[$i]['Added BY'] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
            $final_data[$i]['Added Date'] = date("d/m/Y", strtotime($val->last_updated_date));
            if ($val->is_deleted == 0) {
                $final_data[$i]['GRN Quantity'] = $val->grn_qty;
            }else{
                 $final_data[$i]['GRN Quantity'] = $val->grn_qty . ' (Deleted)';
            }
            $final_data[$i]['UOM'] = $val->uom_name;
            if($val->grn_type ==1|| $val->grn_type==4){
                //$amounts =  $price_by_po[$val->po_number][$val->inventory_id] * $val->grn_qty;
                $get_per_prices =   isset($grn_buyer_rate[$val->po_number]) ? $grn_buyer_rate[$val->po_number] :  $price_by_po[$val->po_number][$val->inventory_id];
                $amounts     =   $get_per_prices * $val->grn_qty;

            //    $final_data[$i]['amounts'] =  formatIndianRupees($amounts);
                $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                $final_data[$i]['amounts']=$formatted_price;
            }elseif($val->grn_type ==2){
                  $amounts                      =  $val->rate * $val->grn_qty;
                //   $final_data[$i]['amounts']    =  formatIndianRupees($amounts);
                $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                $final_data[$i]['amounts']=$formatted_price;
            }else{
                if($val->stock_return_for > '0'){
                    if($get_grn_type[$val->stock_return_for] == 1){
                        //$amounts =  $price_by_po[$po_by_id[$val->stock_return_for]][$val->inventory_id] * $val->grn_qty;
                        $get_per_prices =   isset($grn_buyer_rate[$po_by_id[$val->stock_return_for]]) ? $grn_buyer_rate[$po_by_id[$val->stock_return_for]] :  $price_by_po[$po_by_id[$val->stock_return_for]][$val->inventory_id];
                        $amounts     =   $get_per_prices * $val->grn_qty;
                        // $final_data[$i]['amounts']    =  formatIndianRupees($amounts);
                        $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }elseif($get_grn_type[$val->stock_return_for] == 2){
                        $amounts = $with_po_price[$val->stock_return_for] * $val->grn_qty;
                        // $final_data[$i]['amounts']    =  formatIndianRupees($amounts);
                        $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }elseif($get_grn_type[$val->stock_return_for] == 4){
                        //$amounts =$price_by_po[$manual_po_by_id[$val->stock_return_for]][$val->inventory_id] * $val->grn_qty;
                        $get_per_prices =   isset($grn_buyer_rate[$po_by_id[$val->stock_return_for]]) ? $grn_buyer_rate[$po_by_id[$val->stock_return_for]] :  $price_by_po[$po_by_id[$val->stock_return_for]][$val->inventory_id];
                        $amounts     =   $get_per_prices * $val->grn_qty;
                        // $final_data[$i]['amounts']    =  formatIndianRupees($amounts);
                        $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                        $final_data[$i]['amounts']=$formatted_price;
                    }
                    // $get_po = $po_by_id[$val->stock_return_for];
                }else{
                    $amounts                        =  $val->stock_price * $val->grn_qty;
                    // $final_data[$i]['amounts']      =  formatIndianRupees($amounts);
                    $formatted_price = formatIndianRupees($amounts);
                        if (strpos($formatted_price, '.') === false) {
                            $formatted_price .= '.00';
                        }
                    $final_data[$i]['amounts']=$formatted_price;
                }
            }



            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function get_all_issued_details(){
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inven_id');

            $this->db->select("tp.product_name,tp.category_ids,inv.specification,inv.specification,inv.size,tu.uom_name,issue.id,issue.issued_no,issue.qty,issue.remarks,issue.issued_to,issue.last_updated_date", false);
            $this->db->from("issued_mgt as issue");
            $this->db->join("inventory_mgt as inv",'inv.id=issue.inventory_id', 'LEFT');
            $this->db->join("tbl_product as tp",'tp.product_id=inv.product_id', 'LEFT');
            $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
            $this->db->join("tbl_users as tusr",'tusr.id=issue.last_updated_by', 'LEFT');
            $this->db->where('issue.inventory_id',$inventory_id);
            $this->db->where('issue.is_deleted',0);
            $this->db->order_by('issue.issued_no');
            $query = $this->db->get();
            if($query->num_rows()){
                $response['status']     =   '1';
                $response['data']       =   $query->result();
                $response['message']    =   'Issued details Found';
            }
            else{
                $response['data']         =   [];
                $response['status']       =   '0';
                $response['message']      =   'No Issued details Found';
            }
        }
        echo json_encode($response); die;
    }

    public function update_issued_data(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $issued_ids   =   $this->input->post('issued_ids');
            $issued_qty   =   $this->input->post('issued_qty');
            $users          =   $this->session->userdata('auth_user');
            if($issued_ids && $issued_qty){
                $i=0;
                foreach($issued_ids as $key => $vals){
                    $upd                        =   array();
                    $upd['qty']                 =   $issued_qty[$key];
                    $upd['last_updated_by']     =   $users['users_id'];
                    $upd['last_updated_date']   =   date('Y-m-d H:i:s');
                    $this->db->where('id',$vals);
                    $upqry  = $this->db->update('issued_mgt',$upd);
                    if($upqry){
                        $i++;
                    }
                }
                if($i>=1){
                    $response['status']     =   '1';
                    $response['message']    =   'Issued updated successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Issued not updated, please try again letter';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Issued not found';
            }
        }
        echo json_encode($response); die;
    }
    public function delete_issued_data(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $issued_ids   =   $this->input->post('issued_ids');
            if($issued_ids){
                $upd = array();
                $upd['is_deleted']  =   1;
                $this->db->where_in('id',$issued_ids);
                $qry = $this->db->update('issued_mgt',$upd);
                if($qry){
                    $response['status']     =   '1';
                    $response['message']    =   'Issued deleted successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Issued not deleted, please try again letter';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Issued not found';
            }
        }
        echo json_encode($response); die;
    }

    public function delete_inventory(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $inventory_ids   =   $this->input->post('inventory_ids');
            if($inventory_ids){
                //===verify data delete condition==//
                $z = false;
                $this->db->where_in('id',$inventory_ids);
                $verify_inv_qry = $this->db->select('opening_stock,is_indent')->get_where('inventory_mgt',array());
                if($verify_inv_qry->num_rows()){
                    foreach($verify_inv_qry->result() as $viq_rows){
                        if($viq_rows->opening_stock>=1 || $viq_rows->is_indent==1){
                            $z  =   true;
                        }
                    }
                }
                //===get manual po details==//
                if(!$z){
                    $this->db->where_in('inventory_id',$inventory_ids);
                    $tmpo_qry   =   $this->db->select('inventory_id')->get_where('tbl_manual_po_order',array('order_status' => '1'));
                    if($tmpo_qry->num_rows()){
                        $z  =   true;
                    }
                }
                //===get manual po details==//
                //=====Verify GRN==//
                $this->db->where_in('inventory_id',$inventory_ids);
                $grn_qry = $this->db->select('inventory_id')->get_where('grn_mgt');
                if($grn_qry->num_rows()){
                    $z  =   true;
                }
                //=====Verify GRN==//
                //===verify data delete condition==//
                if(!$z){
                    $this->db->where_in('id',$inventory_ids);
                    $qry = $this->db->delete('inventory_mgt');
                    if($qry){
                        $response['status']     =   '1';
                        $response['message']    =   'Inventory deleted successfully';
                    }
                    else{
                        $response['status']     =   '0';
                        $response['message']    =   'Inventory not deleted, please try again letter';
                    }
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Inventory Already Process';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Inventory not found';
            }
        }
        echo json_encode($response); die;
    }

    public function get_inventory_details(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $inventory_id   =   $this->input->post('inventory_id');
            if($inventory_id){
                $this->db->select("inv.*,tp.prod_name,tp.cat_id", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->where('id',$inventory_id);
                $qry = $this->db->get();
                if($qry->num_rows()){
                    $edit_data=1;
                    $non_edit_env=0;
                    $rfq_qry=$this->db->select('id')->get_where('indent_mgt',array('inventory_id' => $inventory_id));
                    if($rfq_qry->num_rows()){
                        $edit_data=0;
                    }
                    $issue_qry=$this->db->select('id')->get_where('issued_mgt',array('inventory_id' => $inventory_id));
                    if($issue_qry->num_rows()){
                        $edit_data=0;
                        $non_edit_env=1;
                    }
                    $grn_qry=$this->db->select('id')->get_where('grn_mgt',array('inventory_id' => $inventory_id));
                    if($grn_qry->num_rows()){
                        $edit_data=0;
                    }
                    $stock_qry=$this->db->select('id')->get_where('tbl_return_stock',array('inventory_id' => $inventory_id));
                    if($stock_qry->num_rows()){
                        $edit_data=0;
                        $non_edit_env=1;
                    }
                    $response['uom_data']       =   getUOMList();
                    $response['inventory_type'] =   GetInventoryType();
                    $response['data']           =   $qry->row_array();
                    $response['edit_data']      =   $edit_data;
                    $response['non_edit_env']   =   $non_edit_env;
                    $response['status']         =   '1';
                    $response['message']        =   'Inventory Fetch successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Inventory not found';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Inventory not found';
            }
        }
        echo json_encode($response); die;
    }
    public function update_grn_status(){
        //===inventory===//
        $grn_inventory_id                   =   $_POST['grn_inven_id'];
        $grn_id                             =   $_POST['grn_id'];
        $upd                                =   array();
        $upd['vendor_invoice_number']       =   $_POST['vendor_invoice_number'];
        $upd['vehicle_no_lr_no']            =   $_POST['vehicle_no_lr_no'];
        $upd['gross_wt']                    =   $_POST['gross_wt'];
        $upd['gst_no']                      =   $_POST['gst_no'];
        $upd['frieght_other_charges']       =   $_POST['frieght_other_charges'];
        $upd['approved_by']                 =   $_POST['approved_by'];
        $upd['rate']                        =   $_POST['rate'];
        $this->db->where('id',$grn_id);
        $this->db->where('inventory_id',$grn_inventory_id);
        $upd_qry = $this->db->update('grn_mgt',$upd);
        if($upd_qry){
            $response['status']     =   '1';
            $response['message']    =   'GRN details updated successfully';
            echo json_encode($response);die;
        }
        else{
            $response['status']     =   '2';
            $response['message']    =   'No Data Found against inventory';
            echo json_encode($response);die;
        }
    }
    public function update_grn_status_olds_old(){
        //===inventory===//
        $grn_inventory_id                   =   $_POST['grn_inven_id'];
        $upd                                =   array();
        //$upd['grn_qty']                     =   $_POST['enter_grn_qty'];
        $upd['approved_by']                 =   $_POST['approved_by'];
        $upd['rate']                        =   $_POST['rate'];
        $upd['vendor_invoice_number']       =   $_POST['vendor_invoice_number'];
        $upd['vehicle_no_lr_no']            =   $_POST['vehicle_no_lr_no'];
        $upd['gross_wt']                    =   $_POST['gross_wt'];
        $upd['gst_no']                      =   $_POST['gst_no'];
        $upd['frieght_other_charges']       =   $_POST['frieght_other_charges'];
        $total_issued           =   0;
        $total_issued_return    =   0;
        $total_grn_qty    =   0;
        $final_issued_stock_return_qty    =   0;
        $sum_qty = 0;
        $qry_details = $this->db->select('po_number,stock_return_for,grn_type')->get_where('grn_mgt',array('id' => $_POST['grn_id']));
        if($qry_details->num_rows()){
            $grn_type           =    $qry_details->row()->grn_type;
            $stock_return_for   =    $qry_details->row()->stock_return_for;
            $total_order = 0;
            if($grn_type == 1){
                //===get max order qty===//
                $qry_tbl_rfq = $this->db->select('id,rfq_id')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1', 'inventory_id' => $_POST['grn_inven_id']));
                if($qry_tbl_rfq->num_rows()){
                    foreach($qry_tbl_rfq->result() as $rows){
                        $rfq_recod_id[]              =   $rows->id;
                    }
                    $this->db->where_in('rfq_record_id',$rfq_recod_id);
                    $qry_tbl_rfq_price = $this->db->select('id')->get_where('tbl_rfq_price',array());
                    if($qry_tbl_rfq_price->num_rows()){
                        foreach($qry_tbl_rfq_price->result() as $rows){
                            $price_ids[] =  $rows->id;
                        }
                    }
                    $this->db->where_in('price_id',$price_ids);
                    $qry_tbl_rfq_order = $this->db->select('id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => 1));
                    if($qry_tbl_rfq_order->num_rows()){
                        foreach($qry_tbl_rfq_order->result() as $rows){
                            $total_order =  $total_order+$rows->order_quantity;
                        }
                    }
                }
                // pr($qry_tbl_rfq_order->result());
                //===get max order qty===//
                $po_number       =   $qry_details->row()->po_number;
                // $this->db->group_by('inventory_id');
                $qry_grn_qty = $this->db->select('id,grn_qty,is_deleted')->get_where('grn_mgt',array('grn_type'=> 1,'po_number' => $po_number, 'inventory_id' => $grn_inventory_id));
                $grn_ids = array();
                if($qry_grn_qty->num_rows()){
                    $result = $qry_grn_qty->result();
                    foreach($result as $val){
                        if($val->id != $_POST['grn_id'] && $val->is_deleted==0){
                            $sum_qty += $val->grn_qty;
                        }
                        $grn_ids[] = $val->id;
                    }
                    $this->db->where_in('issued_return_for',$grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('is_deleted' => '0'));
                    if($qry_issue_qty->num_rows()){
                        $total_issued = $qry_issue_qty->row()->total_issued;
                    }

                    $this->db->where_in('stock_return_for',$grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array( 'is_deleted' => '0'));

                    if($qry_issue_qty2->num_rows()){
                        $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                    }
                    $final_issued_stock_return_qty  = $total_issued + $total_stock_return;
                    // $total_grn_qty = $qry_grn_qty->row()->total_grn;
                }
            }
            elseif($grn_type == 2){
                $sum_qty = 0;
                $this->db->group_by('inventory_id');
                $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('issued_return_for '=>$_POST['grn_id'], 'is_deleted' => '0'));
                if($qry_issue_qty->num_rows()){
                    $total_issued = $qry_issue_qty->row()->total_issued;
                }
                $this->db->group_by('inventory_id');
                $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array('stock_return_for' => $_POST['grn_id'], 'is_deleted' => '0'));
                if($qry_issue_qty2->num_rows()){
                    $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                }
                $final_issued_stock_return_qty  = $total_issued + $total_stock_return;

            }else{
                $qry_details2 = $this->db->select('po_number,grn_type')->get_where('grn_mgt',array('id' => $stock_return_for));
                if($qry_details2->num_rows()){
                    $grn_type        =    $qry_details2->row()->grn_type;
                    if($grn_type == 1){
                        $po_number       =   $qry_details->row()->po_number;
                        // $this->db->group_by('inventory_id');
                        $qry_grn_qty = $this->db->select('id,grn_qty,is_deleted')->get_where('grn_mgt',array('grn_type'=> 1,'po_number' => $po_number, 'inventory_id' => $grn_inventory_id));
                        $sum_qty = 0;
                        $grn_ids = array();
                        if($qry_grn_qty->num_rows()){
                            $result = $qry_grn_qty->result();
                            foreach($result as $val){
                                if($val->id != $_POST['grn_id'] && $val->is_deleted==0){
                                    $sum_qty += $val->grn_qty;
                                }
                                $grn_ids[] = $val->id;
                            }
                            $this->db->where_in('issued_return_for',$grn_ids);
                            $this->db->group_by('inventory_id');
                            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('is_deleted' => '0'));
                            if($qry_issue_qty->num_rows()){
                                $total_issued = $qry_issue_qty->row()->total_issued;
                            }

                            $this->db->where_in('stock_return_for',$grn_ids);
                            $this->db->group_by('inventory_id');
                            $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array( 'is_deleted' => '0'));

                            if($qry_issue_qty2->num_rows()){
                                $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                            }
                            $final_issued_stock_return_qty  = $total_issued + $total_stock_return;
                            // $total_grn_qty = $qry_grn_qty->row()->total_grn;
                        }
                    }
                    else {
                        $sum_qty = 0;
                        $this->db->group_by('inventory_id');
                        $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('issued_return_for '=>$_POST['grn_id'], 'is_deleted' => '0'));
                        if($qry_issue_qty->num_rows()){
                            $total_issued = $qry_issue_qty->row()->total_issued;
                        }
                        $this->db->group_by('inventory_id');
                        $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array('stock_return_for' => $_POST['grn_id'], 'is_deleted' => '0'));
                        if($qry_issue_qty2->num_rows()){
                            $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                        }
                        $final_issued_stock_return_qty  = $total_issued + $total_stock_return;

                    }
                }
            }
        }
        $total_grn_qty = $sum_qty + $_POST['enter_grn_qty'];
        if($grn_type==1 && $total_order<$total_grn_qty){
            $response['status']     =   '2';
            $response['message']    =   'GRN qty Can not be more Than Order Qty';
        } else if($total_grn_qty < $final_issued_stock_return_qty){
            $response['status']     =   '2';
            $response['message']    =   'GRN qty Can not be Edited';
        } else{
            $this->db->where('id',$_POST['grn_id']);
            $this->db->update('grn_mgt',$upd);
            $grn_qry = $this->db->select('grn_type')->get_where('grn_mgt',array('inventory_id' => $grn_inventory_id, 'id' => $_POST['grn_id']));
            if($grn_qry->num_rows()){
                $grn_type=$grn_qry->row()->grn_type;
                if($grn_type == 2){
                    $response['status']     =   '1';
                    $response['message']    =   'GRN quantity updated successfully';
                    echo json_encode($response);die;
                }
            }
            if(isset($grn_inventory_id) && !empty($grn_inventory_id)){
                $this->db->group_by('inventory_id');
                $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('inventory_id' => $grn_inventory_id, 'indent_qty >=' => '0'));
                $totenv=0;
                if($ind_qry->num_rows()){
                    $totenv=$ind_qry->row()->total_quantity;
                    //=======GRN Qty===//
                    $this->db->group_by('inventory_id');

                    $qry_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inventory_id' => $grn_inventory_id,'grn_type'=>1,'inv_status' => 1,'is_deleted'=> 0));
                    if($qry_grn_env->num_rows()){
                        $tot_grn = $qry_grn_env->row()->total_grn_quantity;
                    }

                    //=======GRN Qty===//
                    $totenv_verient = $totenv*(.02);
                    $tot_verify_env = $totenv-$totenv_verient;

                    if($tot_verify_env<=$tot_grn){
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('indent_mgt',array('inv_status' => '0'));

                        // $this->db->where('inventory_id',$grn_inventory_id);
                        // $this->db->update('order_sub_product',array('inv_status' => '0'));

                        // $this->db->where('inventory_id',$grn_inventory_id);
                        // $this->db->update('tbl_order_confirmation_details',array('inv_status' => '0'));
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('tbl_rfq',array('inv_status' => '0'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('grn_mgt',array('inv_status' => '0'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('grn_mgt',array('inv_status' => '0'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('issued_mgt',array('inv_status' => '0'));

                        $response['status']     =   '1';
                        $response['message']    =   'GRN quantity updated successfully';

                    }else{
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('indent_mgt',array('inv_status' => '1', 'closed_indent' => '0'));
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('tbl_rfq',array('inv_status' => '1'));
                        // $this->db->where('inventory_id',$grn_inventory_id);
                        // $this->db->update('order_sub_product',array('inv_status' => '1'));

                        // $this->db->where('inventory_id',$grn_inventory_id);
                        // $this->db->update('tbl_order_confirmation_details',array('inv_status' => '1'));
                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('grn_mgt',array('inv_status' => '1'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('grn_mgt',array('inv_status' => '1'));

                        $this->db->where('inventory_id',$grn_inventory_id);
                        $this->db->update('issued_mgt',array('inv_status' => '1'));

                        $response['status']     =   '1';
                        $response['message']    =   'GRN quantity updated successfully';
                    }
                }
                else{
                    $response['status']     =   '2';
                    $response['message']    =   'No Data Found against inventory';
                }
            }
        }
        echo json_encode($response);die;
    }
     public function delete_grn(){
        //===inventory===//
        $grn_inventory_id = $_POST['grn_inven_id'];
         $total_issued           =   0;
        $total_issued_return    =   0;
        $total_grn_qty    =   0;
        $final_issued_stock_return_qty    =   0;
        $sum_qty = 0;
        $qry_details = $this->db->select('po_number,stock_return_for,grn_type')->get_where('grn_mgt',array('id' => $_POST['grn_id']));
        if($qry_details->num_rows()){
            $grn_type           =    $qry_details->row()->grn_type;
            $stock_return_for   =    $qry_details->row()->stock_return_for;
            if($grn_type == 1){
                $po_number       =   $qry_details->row()->po_number;
                // $this->db->group_by('inventory_id');
                $qry_grn_qty = $this->db->select('id,grn_qty,is_deleted')->get_where('grn_mgt',array('grn_type'=> 1,'po_number' => $po_number, 'inventory_id' => $grn_inventory_id));
                $grn_ids = array();
                if($qry_grn_qty->num_rows()){
                    $result = $qry_grn_qty->result();
                    foreach($result as $val){
                        if($val->id != $_POST['grn_id'] && $val->is_deleted==0){
                            $sum_qty += $val->grn_qty;
                        }
                        $grn_ids[] = $val->id;
                    }
                    $this->db->where_in('issued_return_for',$grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('is_deleted' => '0'));
                    if($qry_issue_qty->num_rows()){
                        $total_issued = $qry_issue_qty->row()->total_issued;
                    }

                    $this->db->where_in('stock_return_for',$grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array( 'is_deleted' => '0'));

                    if($qry_issue_qty2->num_rows()){
                        $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                    }
                    $final_issued_stock_return_qty  = $total_issued + $total_stock_return;
                    // $total_grn_qty = $qry_grn_qty->row()->total_grn;
                }
            }
            elseif($grn_type == 2){
                $sum_qty = 0;
                $this->db->group_by('inventory_id');
                $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('issued_return_for '=>$_POST['grn_id'], 'is_deleted' => '0'));
                if($qry_issue_qty->num_rows()){
                    $total_issued = $qry_issue_qty->row()->total_issued;
                }
                $this->db->group_by('inventory_id');
                $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array('stock_return_for' => $_POST['grn_id'], 'is_deleted' => '0'));
                if($qry_issue_qty2->num_rows()){
                    $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                }
                $final_issued_stock_return_qty  = $total_issued + $total_stock_return;

            }else{
                $qry_details2 = $this->db->select('po_number,grn_type')->get_where('grn_mgt',array('id' => $stock_return_for));
                if($qry_details2->num_rows()){
                    $grn_type        =    $qry_details2->row()->grn_type;
                    if($grn_type == 1){
                        $po_number       =   $qry_details2->row()->po_number;
                        // pr($po_number);die;
                        // $this->db->group_by('inventory_id');
                        $qry_grn_qty = $this->db->select('id,grn_qty,is_deleted')->get_where('grn_mgt',array('grn_type'=> 1,'po_number' => $po_number, 'inventory_id' => $grn_inventory_id));
                        $sum_qty = 0;
                        $grn_ids = array();
                        if($qry_grn_qty->num_rows()){
                            $result = $qry_grn_qty->result();
                            foreach($result as $val){
                                if($val->id != $_POST['grn_id'] && $val->is_deleted==0){
                                    $sum_qty += $val->grn_qty;
                                }
                                $grn_ids[] = $val->id;
                            }
                            // pr($sum_qty);die;
                            $this->db->where_in('issued_return_for',$grn_ids);
                            $this->db->group_by('inventory_id');
                            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('is_deleted' => '0'));
                            if($qry_issue_qty->num_rows()){
                                $total_issued = $qry_issue_qty->row()->total_issued;
                            }

                            $this->db->where_in('stock_return_for',$grn_ids);
                            $this->db->group_by('inventory_id');
                            $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array( 'is_deleted' => '0'));

                            if($qry_issue_qty2->num_rows()){
                                $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                            }
                            $final_issued_stock_return_qty  = $total_issued + $total_stock_return;
                            // $total_grn_qty = $qry_grn_qty->row()->total_grn;
                        }
                    }
                    else {

                        $sum_qty = 0;
                        $this->db->group_by('inventory_id');
                        $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('issued_return_for '=>$_POST['grn_id'], 'is_deleted' => '0'));
                        if($qry_issue_qty->num_rows()){
                            $total_issued = $qry_issue_qty->row()->total_issued;
                        }
                        $this->db->group_by('inventory_id');
                        $qry_issue_qty2 = $this->db->select('SUM(qty) AS total_stock_return')->get_where('tbl_return_stock',array('stock_return_for' => $_POST['grn_id'], 'is_deleted' => '0'));
                        if($qry_issue_qty2->num_rows()){
                            $total_stock_return = $qry_issue_qty2->row()->total_stock_return;
                        }
                        $final_issued_stock_return_qty  = $total_issued + $total_stock_return;

                    }
                }
            }
        }
        $total_grn_qty = $sum_qty;

        // echo $total_grn_qty.'-'. $final_issued_stock_return_qty.'-'.$sum_qty;die;
        if($total_grn_qty < $final_issued_stock_return_qty){
            $res['status']     =   '2';
            $res['message']    =   'GRN qty Can not be Edited';
        }
        else{
            $this->db->where('id',$_POST['grn_id']);
            $response = $this->db->update('grn_mgt',['is_deleted'=>1]);
            $grn_qry = $this->db->select('grn_type')->get_where('grn_mgt',array('inventory_id' => $grn_inventory_id, 'id' => $_POST['grn_id']));
            if($grn_qry->num_rows()){
                $grn_type=$grn_qry->row()->grn_type;
                if($grn_type == 2){
                    $res['status']     =   '1';
                    $res['message']    =   'GRN Deleted successfully';
                    echo json_encode($res);die;
                }
            }
            if($response && $grn_type == 1){
                $this->db->where('inventory_id',$grn_inventory_id);
                $this->db->update('indent_mgt',array('inv_status' => '1', 'closed_indent' => '0'));

                // $this->db->where('inventory_id',$grn_inventory_id);
                // $this->db->update('order_sub_product',array('inv_status' => '1'));

                // $this->db->where('inventory_id',$grn_inventory_id);
                // $this->db->update('tbl_order_confirmation_details',array('inv_status' => '1'));

                $this->db->where('inventory_id',$grn_inventory_id);
                $this->db->update('tbl_rfq',array('inv_status' => '1'));

                $this->db->where('inventory_id',$grn_inventory_id);
                $this->db->update('grn_mgt',array('inv_status' => '1'));

                $this->db->where('inventory_id',$grn_inventory_id);
                $this->db->update('issued_mgt',array('inv_status' => '1'));
                 $res['status']     =   '1';
                $res['message']    =   'GRN Deleted successfully';
            }elseif($grn_type == 3){
                    $res['status']     =   '1';
                    $res['message']    =   'GRN Deleted successfully';
                    echo json_encode($res);die;
            }
            // else{
            //      $res['status']     =   '2';
            //     $res['message']    =   'SomeThing Went Wrong!';
            // }
        }
            echo json_encode($res);die;

    }
     public function delete_grn_old(){
        //===inventory===//
        $grn_inventory_id = $_POST['grn_inven_id'];
        $this->db->where('id',$_POST['grn_id']);
        $response = $this->db->update('grn_mgt',['is_deleted'=>1]);
        $grn_qry = $this->db->select('grn_type')->get_where('grn_mgt',array('inventory_id' => $grn_inventory_id, 'id' => $_POST['grn_id']));
        if($grn_qry->num_rows()){
            $grn_type=$grn_qry->row()->grn_type;
            if($grn_type == 2){
                $res['status']     =   '1';
                $res['message']    =   'GRN Deleted successfully1';
                echo json_encode($res);die;
            }
        }
        if($response && $grn_type == 1){
            $this->db->where('inventory_id',$grn_inventory_id);
            $this->db->update('indent_mgt',array('inv_status' => '1', 'closed_indent' => '0'));

            // $this->db->where('inventory_id',$grn_inventory_id);
            // $this->db->update('order_sub_product',array('inv_status' => '1'));

            // $this->db->where('inventory_id',$grn_inventory_id);
            // $this->db->update('tbl_order_confirmation_details',array('inv_status' => '1'));

             $this->db->where('inventory_id',$grn_inventory_id);
            $this->db->update('grn_mgt',array('inv_status' => '1'));

            $this->db->where('inventory_id',$grn_inventory_id);
            $this->db->update('grn_mgt',array('inv_status' => '1'));

            $this->db->where('inventory_id',$grn_inventory_id);
            $this->db->update('issued_mgt',array('inv_status' => '1'));
             $res['status']     =   '1';
            $res['message']    =   'GRN Deleted successfully2';
        }
        echo json_encode($res);die;

    }
    public function get_manual_po_report_data()
    {
        $cat_id = array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id    =   $this->session->userdata('auth_user')['users_id'];
        $users      =   $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_manual_po_report_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record   =   $this->inventory_management_model->get_manual_po_report_data($users_ids, $buyer_users, 'total', $cat_id);


        $sr_no = 1;
        $data1 = array();
        foreach ($result as $key => $val) {
            $sub_array = array();
            $sub_array[] = $sr_no;
            $sub_array[] =  "<a href='".base_url('Order/manual_po_details/'.$val->id)."'>".$val->manual_po_number."</a>"  ;
            $sub_array[] = date("d/m/Y", strtotime($val->created_at));
            $sub_array[] = strlen($val->prod_name)<=20 ? $val->prod_name : substr($val->prod_name,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->prod_name.'"></i>';
            //$sub_array[] = $val->specification;
            //$sub_array[] = $val->size;
            //$sub_array[] = $val->inventory_grouping;
            $sub_array[] = $val->store_name;
            $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
            //$sub_array[] = number_format($val->product_quantity,2);
            //$sub_array[] = $val->uom_name;
            $product_total_amount = $val->product_total_amount;
            if($product_total_amount>'1'){
                $formatted_price = formatIndianRupees($product_total_amount);
            }
            else{
                $formatted_price = $product_total_amount >= '.01' ? $product_total_amount : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            // $total_amount =  number_format((float)$val->product_total_amount, 2, '.', '');
            // $total_amount =  IND_money_format($total_amount);
            // if (strpos($total_amount, '.') === false) {
            //     $total_amount .= '.00';
            // }
            $sub_array[] = $formatted_price;
            if($val->order_status==1)
            {
                $sub_array[] = 'Confirmed';
            }else{
                $sub_array[] = 'Cancelled';
            }
            $data1[] = $sub_array;
            $sr_no++;
        }

        // $sr_no = 1;
        // $data1 = array();
        // $manual_po_number = '0';
        // $product_name = array();
        // $manual_product_total_amount = 0;
        // $store_name='';
        // $first_name='';
        // $order_status='';
        // $i=1;
        // //pr($result); die;
        // $j=0;
        // $sub_array = array();
        // foreach ($result as $key => $val) {
        //     if (($manual_po_number != $val->manual_po_number && $manual_po_number!='0') || ($i==$total_record)) {
        //         if($i==1){
        //             if(!in_array($val->prod_name,$product_name)){
        //                 $product_name[] = $val->prod_name;
        //             }

        //             $store_name=$val->store_name;
        //             $first_name=$val->first_name;
        //             $order_status=$val->order_status;
        //             $manual_product_total_amount += floatval($val->product_total_amount);
        //             $sub_array[] = $sr_no;
        //             $sub_array[] = "<a href='" . base_url('Order/manual_po_details/' . $val->id) . "'>" . $val->manual_po_number . "</a>";
        //             $sub_array[] = date("d/m/Y", strtotime($val->created_at));
        //         }
        //         $products_str = implode(', ', $product_name);
        //         // $sub_array[] = $products_str;
        //         $sub_array[] = strlen($products_str)<=20 ? $products_str : substr($products_str,0,20).'<i class="bi bi-info-circle-fill" title="'.$products_str.'"></i>';
        //         $sub_array[] = $store_name;
        //         $sub_array[] = $first_name;
        //         $sub_array[] = number_format($manual_product_total_amount, 2);
        //         $sub_array[] = ($order_status == 1) ? 'Confirmed' : 'Cancelled';
        //         $data1[] = $sub_array;


        //         if($manual_po_number != $val->manual_po_number){
        //             $j++;
        //             $sr_no++;
        //             $manual_po_number = '0';
        //             $product_name = array();
        //             $manual_product_total_amount = 0;
        //             $store_name='';
        //             $first_name='';
        //             $order_status='';

        //             $sub_array = array();
        //             $sub_array[] = $sr_no;
        //             $sub_array[] = "<a href='" . base_url('Order/manual_po_details/' . $val->id) . "'>" . $val->manual_po_number . "</a>";
        //             $sub_array[] = date("d/m/Y", strtotime($val->created_at));
        //             $manual_po_number = $val->manual_po_number;
        //             //$manual_product_total_amount += floatval($val->product_total_amount);
        //         }

        //     }
        //     if ($manual_po_number == '0' && $i!=$total_record) {
        //         $sub_array = array();
        //         $sub_array[] = $sr_no;
        //         $sub_array[] = "<a href='" . base_url('Order/manual_po_details/' . $val->id) . "'>" . $val->manual_po_number . "</a>";
        //         $sub_array[] = date("d/m/Y", strtotime($val->created_at));
        //         $manual_po_number = $val->manual_po_number;
        //         $manual_product_total_amount=0;
        //     }

        //     if($manual_po_number == $val->manual_po_number)
        //     {
        //         if(!in_array($val->prod_name,$product_name)){
        //             $product_name[] = $val->prod_name;
        //         }

        //         $store_name=$val->store_name;
        //         $first_name=$val->first_name;
        //         $order_status=$val->order_status;
        //         $manual_product_total_amount += floatval($val->product_total_amount);


        //     }
        //     $i++;

        //     // $data1[] = $sub_array; // Fixed: Add the row to $data1
        //     // $sr_no++; // Increment the serial number
        // }

        //pr($sub_array); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function export_manual_po_report()
    {

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_manual_po_report_data($users_ids, $buyer_users, 'page');
        // print_r($result);
        $total_record = $this->inventory_management_model->get_manual_po_report_data($users_ids, $buyer_users, 'total');

        $final_data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            $final_data[$i]['Serial Number']        =   $i+1;
            $final_data[$i]['Order Number']         =   $val->manual_po_number;
            $final_data[$i]['Order Date']           =   date("d/m/Y", strtotime($val->created_at));
            $final_data[$i]['Product Name']         =   $val->prod_name;
           //$final_data[$i]['Specification']        =   HtmlDecodeString($val->specification);
            //$final_data[$i]['Size']                 =   HtmlDecodeString($val->size);
            //$final_data[$i]['Inventory Grouping']   =   HtmlDecodeString($val->inventory_grouping);
            $final_data[$i]['Vender Name']          =   ($val->store_name);
            $final_data[$i]['Added BY']             =   $val->first_name; //$val->first_name . ' ' . $val->last_name;
            //$final_data[$i]['Order Quantity']       =   number_format($val->product_quantity,2);
            //$final_data[$i]['UOM']                  =   $val->uom_name;
            $product_total_amount = $val->product_total_amount;
            if($product_total_amount>='1'){
                $formatted_price = formatIndianRupees($product_total_amount);
            }
            else{
                $formatted_price = $product_total_amount >= '.01' ? $product_total_amount : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            // $total_amount =  number_format((float)$val->product_total_amount, 2, '.', '');
            // $total_amount =  IND_money_format($total_amount);
            // if (strpos($total_amount, '.') === false) {
            //     $total_amount .= '.00';
            // }
            $final_data[$i]['Order Value']          =  $formatted_price;
            if($val->order_status==1)
            {
                $final_data[$i]['Status'] = 'Confirmed';
            }else{
                $final_data[$i]['Status'] = 'Cancelled';
            }
            $i++;
        }
        // $i = 0;
        // $final_data = array();
        // $manual_po_number = '0';
        // $product_name = array();
        // $manual_product_total_amount = 0;
        // $store_name='';
        // $first_name='';
        // $order_status='';
        // $j=1;
        // //pr($result); die;
        // foreach ($result as $key => $val) {
        //     if (($manual_po_number != $val->manual_po_number && $manual_po_number!='0') || $j==$total_record) {
        //         if($j==1){
        //             if(!in_array($val->prod_name,$product_name)){
        //                 $product_name[] = $val->prod_name;
        //             }
        //             $store_name=$val->store_name;
        //             $first_name=$val->first_name;
        //             $order_status=$val->order_status;
        //             $manual_product_total_amount += floatval($val->product_total_amount);
        //             $final_data[$i]['Serial Number']  = $i+1;
        //             $final_data[$i]['Order Number'] =  $val->manual_po_number;
        //             $final_data[$i]['Order Date']  = date("d/m/Y", strtotime($val->created_at));

        //         }
        //         $products_str = implode(', ', $product_name);
        //         $final_data[$i]['Product Name'] = $products_str;
        //         $final_data[$i]['Vender Name'] = $store_name;
        //         $final_data[$i]['Added BY'] = $first_name;
        //         $final_data[$i]['Order Value'] = number_format($manual_product_total_amount, 2);
        //         $final_data[$i]['Status'] = ($order_status == 1) ? 'Confirmed' : 'Cancelled';
        //         $i++;
        //         if($manual_po_number != $val->manual_po_number && $manual_po_number!='0' && $j!=$total_record){
        //             $manual_po_number = '0';
        //             $product_name = array();
        //             $manual_product_total_amount = 0;
        //             $store_name='';
        //             $first_name='';
        //             $order_status='';

        //             $sub_array = array();
        //             $final_data[$i]['Serial Number'] = $i+1;
        //             $final_data[$i]['Order Number'] =  $val->manual_po_number;
        //             $final_data[$i]['Order Date'] = date("d/m/Y", strtotime($val->created_at));
        //             $manual_po_nufinal_datamber = $val->manual_po_number;
        //             $manual_product_total_amount += floatval($val->product_total_amount);
        //         }

        //     }
        //     if ($manual_po_number == '0' && $j!=$total_record) {
        //         $final_data[$i]['Serial Number']  = $i+1;
        //         $final_data[$i]['Order Number'] =  $val->manual_po_number;
        //         $final_data[$i]['Order Date']  = date("d/m/Y", strtotime($val->created_at));
        //         $manual_po_number = $val->manual_po_number;
        //     }

        //     if($manual_po_number == $val->manual_po_number)
        //     {
        //         if(!in_array($val->prod_name,$product_name)){
        //             $product_name[] = $val->prod_name;
        //         }

        //         $store_name=$val->store_name;
        //         $first_name=$val->first_name;
        //         $order_status=$val->order_status;
        //         $manual_product_total_amount += floatval($val->product_total_amount);

        //     }

        //     $j++;
        //     // $data1[] = $sub_array; // Fixed: Add the row to $data1
        //     // $sr_no++; // Increment the serial number
        // }

        //pr($final_data); die;
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }
    public function manual_po_report(){
        // if (
        //     !authorize($this->access['RFQ']['ORDERS_CONFIRMED']["create"]) &&
        //     !authorize($this->access['RFQ']['ORDERS_CONFIRMED']["edit"]) &&
        //     !authorize($this->access['RFQ']['ORDERS_CONFIRMED']["view"]) &&
        //     !authorize($this->access['RFQ']['ORDERS_CONFIRMED']["delete"])
        // ) {
        //     unAuthorizedBuyer();
        // }
        $this->load->model("Buyer_model");
        $data['page_title'] = "Manual Po Report";
        // $data['__show_sidebar_popup'] = true;
        $data['cat_subcat']     =  '';//gat_all_cat_subcat();
        $data['all_division'] = get_all_divisions_selected();
        $data['buyer_factory_details'] = getBuyerBranchList();
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        $data['currency_list']      =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']     =   $buyer_currency;
        $this->load->view('inventory_management/manual_po_report_list',$data);
    }
    public function get_issued_details_issue_id(){
        if($this->input->is_ajax_request()){
            $id   =   $this->input->post('id');
            $inven_id   =   $this->input->post('inven_id');

            $this->db->select("tp.prod_name product_name,tp.cat_id,inv.specification,inv.specification,inv.size,tu.uom_name,issue.id,issue.issued_no,issue.qty,issue.remarks,issue.issued_to,issue.last_updated_date,inv.opening_stock", false);
            $this->db->from("issued_mgt as issue");
            $this->db->join("inventory_mgt as inv",'inv.id=issue.inventory_id', 'LEFT');
            $this->db->join("view_live_master_product_with_alias as tp",'tp.prod_id=inv.product_id', 'LEFT');
            $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
            $this->db->join("tbl_users as tusr",'tusr.id=issue.last_updated_by', 'LEFT');
            $this->db->where('issue.id',$id);
            $this->db->where('issue.inventory_id',$inven_id);
            $this->db->where('issue.is_deleted',0);
            $this->db->order_by('issue.issued_no');
            $query = $this->db->get();
            if($query->num_rows()){
               $inventory_details['inv'] =  $query->result();
                $total_grn  =   0;
                $this->db->where('inventory_id',$inven_id);
                $this->db->group_by('inventory_id');
                $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt');
                if($qry_tot_grn_env->num_rows()){
                    $total_grn    =   $qry_tot_grn_env->row()->total_grn_quantity;
                }

                $total_issued  =   0;
                $this->db->where('inventory_id',$inven_id);
                $this->db->where('is_deleted',0);
                $this->db->group_by('inventory_id');
                $qry_issued_env = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt');
                if($qry_issued_env->num_rows()){
                    $total_issued  =   $qry_issued_env->row()->total_issued;
                }
                $total_return_stock  =   0;
                $this->db->where('inventory_id',$inven_id);
                $this->db->where('is_deleted',0);
                $this->db->group_by('inventory_id');
                $qry_stock_ret = $this->db->select('SUM(qty) AS total_return_stock')->get_where('tbl_return_stock');
                if($qry_stock_ret->num_rows()){
                    $total_return_stock  =   $qry_stock_ret->row()->total_return_stock;
                }
                $total_issued_return  =   0;
                $this->db->where('inventory_id',$inven_id);
                $this->db->where('is_deleted',0);
                $this->db->group_by('inventory_id');
                $qry_issued_return = $this->db->select('SUM(qty) AS total_issued_return')->get_where('issued_return_mgt');
                if($qry_issued_return->num_rows()){
                    $total_issued_return  =   $qry_issued_return->row()->total_issued_return;
                }
                $response['current_stock']      =   ($inventory_details['inv'][0]->opening_stock + $total_grn+$total_issued_return) - ($total_issued+$total_return_stock);
                $response['status']             =   '1';
                $response['data']               =   $query->result();
                $response['message']            =   'Issued details Found';
            }
            else{
                $response['data']         =   [];
                $response['status']       =   '0';
                $response['message']      =   'No Issued details Found';
            }
        }
        echo json_encode($response); die;
    }
    public function update_issue_status(){
        $total_issued           =   0;
        $total_issued_return    =   0;
        $qry_details = $this->db->select('inventory_id,issued_return_for')->get_where('issued_mgt',array('id' => $_POST['id']));
        if($qry_details->num_rows()){
            $inventory_id       =   $qry_details->row()->inventory_id;
            $issued_return_for  =   $qry_details->row()->issued_return_for;
            $this->db->group_by('inventory_id');
            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('id !='=>$_POST['id'],'issued_return_for' => $issued_return_for, 'inventory_id' => $inventory_id, 'is_deleted' => '0'));
            if($qry_issue_qty->num_rows()){
                $total_issued = $qry_issue_qty->row()->total_issued;
            }
            $total_issued = $total_issued+$_POST['current_qty'];
            $this->db->group_by('inventory_id');
            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued_return')->get_where('issued_return_mgt',array('issued_return_for' => $issued_return_for, 'inventory_id' => $inventory_id, 'is_deleted' => '0'));
            if($qry_issue_qty->num_rows()){
                $total_issued_return = $qry_issue_qty->row()->total_issued_return;
            }
        }

        if($total_issued>=$total_issued_return){
            $this->db->where('id',$_POST['id']);
            $res = $this->db->update('issued_mgt',['qty'=>$_POST['current_qty']]);
            if($res){
                $response['status']     =   '1';
                $response['message']    =   'Issued quantity updated successfully';
            }else{
                $response['status']     =   '2';
                $response['message']    =   'No Data Found against inventory';
            }
        }
        else{
            $response['status']     =   '2';
            $response['message']    =   'Issued Qty should be greater than Issued return Qty';
        }
        echo json_encode($response);die;
    }
    public function delete_issued(){
           $total_issued           =   0;
        $total_issued_return    =   0;
        $qry_details = $this->db->select('inventory_id,issued_return_for')->get_where('issued_mgt',array('id' => $_POST['id']));
        if($qry_details->num_rows()){
            $inventory_id       =   $qry_details->row()->inventory_id;
            $issued_return_for  =   $qry_details->row()->issued_return_for;
            $this->db->group_by('inventory_id');
            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt',array('id !='=>$_POST['id'],'issued_return_for' => $issued_return_for, 'inventory_id' => $inventory_id, 'is_deleted' => '0'));
            if($qry_issue_qty->num_rows()){
                $total_issued = $qry_issue_qty->row()->total_issued;
            }
            $this->db->group_by('inventory_id');
            $qry_issue_qty = $this->db->select('SUM(qty) AS total_issued_return')->get_where('issued_return_mgt',array('issued_return_for' => $issued_return_for, 'inventory_id' => $inventory_id, 'is_deleted' => '0'));
            if($qry_issue_qty->num_rows()){
                $total_issued_return = $qry_issue_qty->row()->total_issued_return;
            }
        }

        if($total_issued>=$total_issued_return){
            $this->db->where('id',$_POST['id']);
            $res = $this->db->update('issued_mgt',['is_deleted'=>1]);
            if($res){
                $response['status']     =   '1';
                $response['message']    =   'GRN Deleted successfully';
            }else{
                $response['status']     =   '2';
                $response['message']    =   'SomeThing Went Wrong!';
            }
        }
        else{
            $response['status']     =   '2';
            $response['message']    =   'Issued Qty should be greater than Issued return Qty';
        }
            echo json_encode($response);die;
    }

    public function get_inventory_for_return_stock(){
        if($this->input->is_ajax_request()){
            $inventory  =   $this->input->post('inventory',true);
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            if($inventory){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $inventory_details['inv']   =   $query->row_array();
                    $data                       =   $query->row();
                    $final_arr                  =   array();
                    $final_arr_qty              =   array();
                    if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){
                        $final_arr['0']     =   'Opening Stock';
                        $final_arr_qty['0'] =   $data->opening_stock;
                    }
                    //===GRN Details===//
                // $this->db->select('SUM(grn_qty) as total_grn_quantity, MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                // $this->db->where('inventory_id',$inventory);
                // $this->db->group_by('inventory_id');
                // $this->db->group_by('po_number');
                // $this->db->where('is_deleted',0);
                // $this->db->where('grn_type',1);
                // $qry_grn = $this->db->get('grn_mgt');
                $this->db->select('grn_qty as total_grn_quantity,id,vendor_name,grn_type,order_no,order_id,inventory_id,po_number');
                $this->db->where('inventory_id',$inventory);
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type',1);
                $qry_grn = $this->db->get('grn_mgt');

                $get_store_name =   array();
                $grn_datas =   array();
                if($qry_grn->num_rows()){
                    $grn_datas = $qry_grn->result();

                    $po_number = array();
                    foreach($qry_grn->result() as $key=> $val){
                        if($val->grn_type == 1){
                            $po_number[] = $val->po_number;
                        }
                    }

                    //==Store Details==//
                    // $get_store_name =   array();
                    if(isset($po_number) && !empty($po_number)){
                        $this->db->select('tro.po_number,trp.vend_user_id,ts.store_name');
                        $this->db->join('tbl_rfq_price as trp','trp.id =tro.price_id');
                        $this->db->join('tbl_users as tu','tu.id =trp.vend_user_id');
                        $this->db->join('tbl_store as ts','ts.store_id =tu.store_id');
                        $this->db->where_in('tro.po_number',$po_number);
                        $qry = $this->db->get('tbl_rfq_order tro');
                        if($qry->num_rows()){
                            foreach($qry->result() as $keys => $vals){
                                $get_store_name[$vals->po_number] =$vals->store_name;
                            }
                        }
                    }
                    //==Store Details==//
                }
                 ///manual po
                 //$this->db->select('SUM(grn_qty) as total_grn_quantity, MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                 $this->db->select('grn_qty as total_grn_quantity, id,vendor_name,grn_type, order_no,order_id,inventory_id,po_number');
                 $this->db->where('inventory_id',$inventory);
                 //$this->db->group_by('inventory_id');
                 //$this->db->group_by('po_number');
                 $this->db->where('is_deleted',0);
                 $this->db->where('grn_type',4);
                 $qry_manual_grn = $this->db->get('grn_mgt');

                 if($qry_manual_grn->num_rows()){
                     $grn_datas =array_merge($grn_datas , $qry_manual_grn->result());
                     $manual_po_number = array();
                     foreach($qry_manual_grn->result() as $key=> $val){
                         if($val->grn_type == 4){
                             $manual_po_number[] = $val->po_number;
                         }
                     }

                     //==Store Details==//
                     // $get_store_name =   array();
                     if(isset($manual_po_number) && !empty($manual_po_number)){
                         $this->db->select('mpo.manual_po_number,mpo.vendor_id as vend_user_id,ts.store_name');
                         $this->db->join('tbl_store as ts','ts.store_id =mpo.vendor_id');
                         $this->db->where_in('mpo.manual_po_number',$manual_po_number);
                         $qry = $this->db->get('tbl_manual_po_order mpo');

                         if($qry->num_rows()){
                             foreach($qry->result() as $keys => $vals){
                                 $get_store_name[$vals->manual_po_number] =$vals->store_name;
                             }
                         }
                     }
                     //==Store Details==//
                 }
                $this->db->select('grn_qty as total_grn_quantity, id,vendor_name,grn_type,order_no,order_id,inventory_id,po_number');
                $this->db->where('inventory_id',$inventory);
                //$this->db->group_by('inventory_id');
                //$this->db->group_by('po_number');
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type',2);
                $qry_grn2 = $this->db->get('grn_mgt');
                // pr($qry_grn2->result());die;
                if($qry_grn2->num_rows()){
                    if(isset($grn_datas)){
                        // $grn_datas = $grn_datas+$qry_grn2->result();
                        $grn_datas = array_merge($grn_datas , $qry_grn2->result());
                    }
                    else{
                        $grn_datas = $qry_grn2->result();
                    }
                }

                //===get grn again stock return===//
                $stock_grn_add_qty = array();
                // $this->db->group_by('inventory_id');
                $this->db->group_by('stock_return_for');
                $stock_grn_qry = $this->db->select('SUM(grn_qty) as total_grn_quantity,MAX(stock_return_for) as stock_return_for')->get_where('grn_mgt',array('inventory_id' => $inventory, 'grn_type' => '3', 'is_deleted' => '0'));
                if($stock_grn_qry->num_rows()){
                    foreach($stock_grn_qry->result() as $stgrn_row){
                        if($stgrn_row->stock_return_for==0){
                            $final_arr_qty['0'] =   $final_arr_qty['0']+$stgrn_row->total_grn_quantity;
                        }
                        else{
                            $stock_grn_add_qty[$stgrn_row->stock_return_for]=$stgrn_row->total_grn_quantity;
                        }
                    }
                }

                //===get grn again stock return===//
                    //===issued data===//
                    $total_issued               =   0;
                    $total_issued_for    = array();
                    $this->db->where('inventory_id',$inventory);
                    // $this->db->group_by('inventory_id');
                    //$this->db->group_by('issued_return_for');
                    //$qry_issued = $this->db->select('SUM(qty) AS total_issued,MAX(issued_return_for) as issued_return_for,MAX(inventory_id) as inventory_id')->get_where('issued_mgt', array('is_deleted' => '0'));
                    $qry_issued = $this->db->select('qty as total_issued,issued_return_for')->get_where('issued_mgt', array('is_deleted' => '0'));
                    if($qry_issued->num_rows()){
                        foreach($qry_issued->result() as $isrw){
                            if($isrw->issued_return_for==0){
                                $final_arr_qty['0'] = $final_arr_qty['0']-$isrw->total_issued;
                            }
                            else{
                                if(isset($total_issued_for[$isrw->issued_return_for])){
                                    $total_issued_for[$isrw->issued_return_for]=$total_issued_for[$isrw->issued_return_for]+$isrw->total_issued;
                                }
                                else{
                                    $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                                }
                            }
                            // $total_issued  =   $isrw->total_issued;
                            // if($isrw->issued_return_for==0){
                            //     $final_arr_qty['0'] = $final_arr_qty['0']-$isrw->total_issued;
                            // }
                            // $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                            // if(isset($total_issued_for[$isrw->inventory_id])){
                            //     $total_issued_for[$isrw->inventory_id]=$total_issued_for[$isrw->inventory_id]+$isrw->total_issued;
                            // }else{
                            //     $total_issued_for[$isrw->inventory_id]=$isrw->total_issued;
                            // }
                        }
                    }
                    //pr($total_issued_for); die;
                    //===issued data===//
                    //pr($grn_datas); die;
                    if(isset($grn_datas) && !empty($grn_datas)){
                        foreach($grn_datas as $key => $vals){
                            if($vals->grn_type == 2){
                                $final_arr[$vals->id] =  $vals->order_no.'/'.$vals->vendor_name;
                            }else{
                                $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                            }
                            if(isset($stock_grn_add_qty[$vals->id]) && !empty($stock_grn_add_qty[$vals->id])){
                                $final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->id];
                                //$final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->inventory_id];
                            }
                            else{
                                $final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->id];
                                //$final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->inventory_id];
                            }
                        }
                    }
                    //pr($final_arr_qty); die;
                    // pr($final_arr);die;
                    //===Issued Return Data===//
                    $this->db->where('inventory_id',$inventory);
                    // $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $qry_issued_return = $this->db->select('SUM(qty) AS total_issued_return,MAX(issued_return_for) as issued_return_for')->get_where('issued_return_mgt', array('is_deleted' => '0'));
                    if($qry_issued_return->num_rows()){
                        foreach($qry_issued_return->result() as $isretw){
                            if($isretw->issued_return_for==0){
                                $final_arr_qty['0'] = $final_arr_qty['0']+$isretw->total_issued_return;
                            }
                            else{
                                $final_arr_qty[$isretw->issued_return_for]=$final_arr_qty[$isretw->issued_return_for] + $isretw->total_issued_return;
                            }
                        }
                    }
                    //===Issued Return Data===//
                    //===Stock Return===//
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('stock_return_for');
                    $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(stock_return_for) as stock_return_for')->get_where('tbl_return_stock', array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    if($qry_stock_return->num_rows()){
                        foreach($qry_stock_return->result() as $stk_row){
                            $final_arr_qty[$stk_row->stock_return_for]=$final_arr_qty[$stk_row->stock_return_for] - $stk_row->total_stock_return;
                        }
                    }
                    //===Stock Return===//
                    //pr($final_arr); die;
                    //pr($final_arr_qty); die;
                    $chk_array=array();
                    foreach($final_arr as $flkey => $flvals){
                        if(isset($chk_array[$flvals])){
                                $final_arr_qty[$flkey] = $final_arr_qty[$flkey]+$final_arr_qty[$chk_array[$flvals]];
                                unset($final_arr[$chk_array[$flvals]]);
                                unset($final_arr_qty[$chk_array[$flvals]]);
                        }
                        else{
                                $chk_array[$flvals]=$flkey;
                        }
                    }
                    foreach($final_arr_qty as $fkey => $fvals){
                        if($fvals<=0){
                            unset($final_arr_qty[$fkey]);
                            unset($final_arr[$fkey]);
                        }
                    }

                    $issued_type = array();
                    $qry_issued_type    =   $this->db->select('id,name')->get_where('issued_type',array('status' => '1'));
                    if($qry_issued_type->num_rows()){
                        foreach($qry_issued_type->result() as $isu_tp){
                            $issued_type[$isu_tp->id] = $isu_tp->name;
                        }
                    }
                    if(isset($final_arr_qty) && !empty($final_arr_qty)){
                        $resp['my_default_stock']   =   isset($final_arr_qty[0]) ? $final_arr_qty[0]:'0';
                        $resp['inv_id']             =   $data->id;
                        $resp['added_qty']          =   isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                        $resp['branch_id']          =   $data->branch_id;
                        $resp['comp_br_sp_inv_id']  =   $data->comp_br_sp_inv_id;
                        $resp['product_id']         =   $data->product_id;
                        $resp['specification']      =   $data->specification;
                        $resp['size']               =   $data->size;
                        $resp['uom']                =   $data->uom;
                        $resp['product_name']       =   $data->prod_name;
                        $resp['uom_name']           =   $data->uom_name;
                        $resp['issued_type']        =   $issued_type;
                        $res['status']              =   1;
                        $res['return_for']          =   $final_arr;
                        $res['return_for_qty']      =   $final_arr_qty;
                        $res['message']             =   'Inventory found';
                        $res['data']                =   $resp;
                        echo json_encode($res); die;
                    }
                    else{
                        $res['status']          =   2;
                        $res['message']         =   'Error, Stock Not Found';
                        echo json_encode($res); die;
                    }
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function get_inventory_for_return_stock_old__(){
        if($this->input->is_ajax_request()){
            $inventory  =   $this->input->post('inventory',true);
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            if($inventory){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $inventory_details['inv']   =   $query->row_array();
                    $data                       =   $query->row();
                    $final_arr                  =   array();
                    $final_arr_qty              =   array();
                    if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){
                        $final_arr['0']     =   'Opening Stock';
                        $final_arr_qty['0'] =   $data->opening_stock;
                    }
                    //===GRN Details===//
                    $this->db->select('SUM(grn_qty) as total_grn_quantity , MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('po_number');
                    $this->db->where('is_deleted',0);
                    $this->db->where('grn_type =',1);
                    $qry_grn = $this->db->get('grn_mgt');
                    if($qry_grn->num_rows()){
                        $grn_datas = $qry_grn->result();
                        $po_number = array();
                        foreach($qry_grn->result() as $key=> $val){
                            if($val->grn_type == 1){
                                $po_number[] = $val->po_number;
                            }
                        }
                        //===get grn again stock return===//
                        $stock_grn_add_qty = array();
                        $this->db->group_by('inventory_id');
                        $this->db->group_by('stock_return_for');
                        $stock_grn_qry = $this->db->select('SUM(grn_qty) as total_grn_quantity,MAX(stock_return_for) as stock_return_for')->get_where('grn_mgt',array('inventory_id' => $inventory, 'grn_type' => '3', 'is_deleted' => '0'));
                        if($stock_grn_qry->num_rows()){
                            foreach($stock_grn_qry->result() as $stgrn_row){
                                if($stgrn_row->stock_return_for==0){
                                    $final_arr_qty['0'] =   $final_arr_qty['0']+$stgrn_row->total_grn_quantity;
                                }
                                else{
                                    $stock_grn_add_qty[$stgrn_row->stock_return_for]=$stgrn_row->total_grn_quantity;
                                }
                            }
                        }
                        //===get grn again stock return===//
                        //==Store Details==//
                        $get_store_name =   array();
                        if(isset($po_number) && !empty($po_number)){
                            $this->db->select('tro.po_number,trp.vend_user_id,ts.store_name');
                            $this->db->join('tbl_rfq_price as trp','trp.id =tro.price_id');
                            $this->db->join('tbl_users as tu','tu.id =trp.vend_user_id');
                            $this->db->join('tbl_store as ts','ts.store_id =tu.store_id');
                            $this->db->where_in('tro.po_number',$po_number);
                            $qry = $this->db->get('tbl_rfq_order tro');
                            if($qry->num_rows()){
                                foreach($qry->result() as $keys => $vals){
                                    $get_store_name[$vals->po_number] =$vals->store_name;
                                }
                            }
                        }
                        //==Store Details==//
                    }
                    //===GRN Details===//
                    $this->db->select('grn_qty as total_grn_quantity, id,vendor_name,grn_type,order_no,order_id,inventory_id,po_number');
                $this->db->where('inventory_id',$inventory_id);
                //$this->db->group_by('inventory_id');
                //$this->db->group_by('po_number');
                $this->db->where('is_deleted',0);
                $this->db->where('grn_type',2);
                $qry_grn2 = $this->db->get('grn_mgt');
                // echo $this->db->last_query();die;
                if($qry_grn2->num_rows()){
                    if(isset($grn_datas)){
                        // pr($grn_datas);die;
                        $grn_datas = $grn_datas+$qry_grn2->result();
                    }
                    else{
                        $grn_datas = $qry_grn2->result();
                    }

                }

                    //===issued data===//
                    $total_issued               =   0;
                    $total_issued_for    = array();
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $qry_issued = $this->db->select('SUM(qty) AS total_issued,MAX(issued_return_for) as issued_return_for')->get_where('issued_mgt', array('is_deleted' => '0'));
                    if($qry_issued->num_rows()){
                        foreach($qry_issued->result() as $isrw){
                            $total_issued  =   $isrw->total_issued;
                            if($isrw->issued_return_for==0){
                                $final_arr_qty['0'] = $final_arr_qty['0']-$isrw->total_issued;
                            }
                            $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued;
                        }
                    }
                    //===issued data===//

                    if(isset($grn_datas) && !empty($grn_datas)){
                        foreach($grn_datas as $key => $vals){
                            if($vals->grn_type == 2){
                                $final_arr[$vals->id] =  $vals->order_no.'/'.$vals->vendor_name;
                            }else{
                                $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                            }
                            if(isset($stock_grn_add_qty[$vals->id]) && !empty($stock_grn_add_qty[$vals->id])){
                                $final_arr_qty[$vals->id] =  (($vals->total_grn_quantity)+$stock_grn_add_qty[$vals->id])-$total_issued_for[$vals->id];
                            }
                            else{
                                $final_arr_qty[$vals->id] =  ($vals->total_grn_quantity)-$total_issued_for[$vals->id];
                            }
                        }
                    }
                    //===Issued Return Data===//
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $qry_issued_return = $this->db->select('SUM(qty) AS total_issued_return,MAX(issued_return_for) as issued_return_for')->get_where('issued_return_mgt', array('is_deleted' => '0'));
                    if($qry_issued_return->num_rows()){
                        foreach($qry_issued_return->result() as $isretw){
                            if($isretw->issued_return_for==0){
                                $final_arr_qty['0'] = $final_arr_qty['0']+$isretw->total_issued_return;
                            }
                            else{
                                $final_arr_qty[$isretw->issued_return_for]=$final_arr_qty[$isretw->issued_return_for] + $isretw->total_issued_return;
                            }
                        }
                    }
                    //===Issued Return Data===//
                    //===Stock Return===//
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('stock_return_for');
                    $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(stock_return_for) as stock_return_for')->get_where('tbl_return_stock', array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    if($qry_stock_return->num_rows()){
                        foreach($qry_stock_return->result() as $stk_row){
                            $final_arr_qty[$stk_row->stock_return_for]=$final_arr_qty[$stk_row->stock_return_for] - $stk_row->total_stock_return;
                        }
                    }

                    //===Stock Return===//
                    foreach($final_arr_qty as $fkey => $fvals){
                        if($fvals<=0){
                            unset($final_arr_qty[$fkey]);
                            unset($final_arr[$fkey]);
                        }
                    }

                    $issued_type = array();
                    $qry_issued_type    =   $this->db->select('id,name')->get_where('issued_type',array('status' => '1'));
                    if($qry_issued_type->num_rows()){
                        foreach($qry_issued_type->result() as $isu_tp){
                            $issued_type[$isu_tp->id] = $isu_tp->name;
                        }
                    }
                    if(isset($final_arr_qty) && !empty($final_arr_qty)){
                        $resp['my_default_stock']   =   isset($final_arr_qty[0]) ? $final_arr_qty[0]:'0';
                        $resp['inv_id']             =   $data->id;
                        $resp['added_qty']          =   isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                        $resp['branch_id']          =   $data->branch_id;
                        $resp['comp_br_sp_inv_id']  =   $data->comp_br_sp_inv_id;
                        $resp['product_id']         =   $data->product_id;
                        $resp['specification']      =   $data->specification;
                        $resp['size']               =   $data->size;
                        $resp['uom']                =   $data->uom;
                        $resp['product_name']       =   $data->prod_name;
                        $resp['uom_name']           =   $data->uom_name;
                        $resp['issued_type']        =   $issued_type;
                        $res['status']              =   1;
                        $res['return_for']          =   $final_arr;
                        $res['return_for_qty']      =   $final_arr_qty;
                        $res['message']             =   'Inventory found';
                        $res['data']                =   $resp;
                        echo json_encode($res); die;
                    }
                    else{
                        $res['status']          =   2;
                        $res['message']         =   'Error, Stock Not Found';
                        echo json_encode($res); die;
                    }
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }

    public function get_inventory_for_return_stock_salte(){
        if($this->input->is_ajax_request()){
            $inventory  =   $this->input->post('inventory',true);
             $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
            if($inventory){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.product_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product as tp",'tp.product_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $data   =   $query->row();
                    $inventory_details =  $query->row();
                    $total_grn  =   0;
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt');
                    if($qry_tot_grn_env->num_rows()){
                        $total_grn    =   $qry_tot_grn_env->row()->total_grn_quantity;
                    }

                    $total_issued  =   0;
                    $this->db->where('inventory_id',$inventory);
                    $this->db->where('is_deleted',0);
                    $this->db->group_by('inventory_id');
                    $qry_issued_env = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt');
                    if($qry_issued_env->num_rows()){
                        $total_issued  =   $qry_issued_env->row()->total_issued;
                    }

                    $resp    =   array();
                    //$resp['current_stock']     =   ($inventory_details->opening_stock + $total_grn) - ($total_issued);
                    $resp['current_stock']     =   ($inventory_details->opening_stock);
                    $qry_tot_qty_rs = $this->db->select('SUM(qty) AS added_qty')->get_where('tbl_return_stock',['inventory_id'=>$inventory,'company_id'=>$company_id,'is_deleted'=>'0','branch_id'=>$data->branch_id]);
                    //echo $this->db->last_query();die;
                    if($qry_tot_qty_rs->num_rows()){
                        $rs_added_qty    =   $qry_tot_qty_rs->row()->added_qty;
                    }
                   $final_arr = array();
                   $final_arr_qty = array();
                   //pr($data);die;
                   $all_issued_qty = array();
                   $this->db->select('SUM(qty) as total_issued_qty ,MAX(inventory_id) as inventory_id,MAX(issued_return_for) as issued_return_for');
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_total_issued_qty = $this->db->get('issued_mgt');

                    if($qry_total_issued_qty->num_rows()){
                        foreach($qry_total_issued_qty->result() as $keys =>$vals){
                            $all_issued_qty[$vals->issued_return_for] = $vals->total_issued_qty;
                        }
                    }
                     $all_issued_return_qty = array();
                   $this->db->select('SUM(qty) as total_issued_qty ,MAX(inventory_id) as inventory_id,MAX(issued_return_for) as issued_return_for');
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_total_issued_return_qty = $this->db->get('issued_return_mgt');
                    if($qry_total_issued_return_qty->num_rows()){
                        foreach($qry_total_issued_return_qty->result() as $keys =>$vals){
                             $all_issued_return_qty[$vals->issued_return_for] = $vals->total_issued_qty;
                        }
                    }
                    // pr($all_issued_qty);die;
                   if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){

                       $final_arr['0'] = 'Opening Stock';
                       $final_arr_qty['0'] = $resp['current_stock'];
                   }
                   $this->db->select('SUM(grn_qty) as total_grn_quantity , MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('po_number');
                    $this->db->where('is_deleted',0);
                     $qry_grn = $this->db->get('grn_mgt gm');
                    if($qry_grn->num_rows()){
                        $po_number = array();
                        $grn_datas = $qry_grn->result();
                        foreach($qry_grn->result() as $key=> $val){
                            if($val->grn_type == 1){

                                $po_number[] = $val->po_number;
                            }
                        }
                            $get_store_name= array();
                         if(isset($po_number) && !empty($po_number)){
                             $this->db->select('ocd.po_number,ocd.vendor_id,store_name');
                             $this->db->where_in('ocd.po_number',$po_number);
                             $this->db->join('tbl_store as s','s.store_id =ocd.vendor_id');
                             $qry = $this->db->get('tbl_order_confirmation_details ocd');
                             if($qry->num_rows()){
                                $res_data = $qry->result();
                                // pr($res_data);die;
                                foreach($res_data as $keys => $vals){
                                    $get_store_name[$vals->po_number] =$vals->store_name;
                                }
                                // $get_store_name[$qry->row()->po_number] =$qry->row()->store_name;
                            }
                        }
                        foreach($grn_datas as $key => $vals){
                            if($vals->grn_type == 2){
                                $final_arr[$vals->id] =  $vals->po_number.'/'.$vals->vendor_name;
                            }else{
                                $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                            }
                            $final_arr_qty[$vals->id] =  ($vals->total_grn_quantity ) - ($all_issued_qty[$vals->id]);
                        }
                    }
                    $this->db->select('SUM(qty) as total_grn_quantity ,MAX(stock_return_for) as stock_return_for,MAX(inventory_id) as inventory_id');
                    $this->db->where('inventory_id',$inventory);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('stock_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_return_stock = $this->db->get('tbl_return_stock');
                    if($qry_return_stock->num_rows()){
                         foreach($qry_return_stock->result() as $keys => $value){
                            $final_arr_qty[$value->stock_return_for] = ($final_arr_qty[$value->stock_return_for] - $value->total_grn_quantity) + $all_issued_return_qty[$value->stock_return_for];
                         }
                    }
                    $resp['my_default_stock']  =  isset($final_arr_qty[0]) ? $final_arr_qty[0]:'0';
                    $resp['inv_id']              =   $data->id;
                    $resp['added_qty']              =  isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                    $resp['branch_id']              =   $data->branch_id;
                    $resp['comp_br_sp_inv_id']   =   $data->comp_br_sp_inv_id;
                    $resp['product_id']          =   $data->product_id;
                    $resp['specification']       =   $data->specification;
                    $resp['size']                =   $data->size;
                    $resp['uom']                 =   $data->uom;
                    $resp['product_name']        =   $data->product_name;
                    $resp['uom_name']            =   $data->uom_name;

                    $res['status']              =   1;
                    $res['return_for']              = $final_arr;
                    $res['return_for_qty']              = $final_arr_qty;
                    $res['message']             =   'Inventory found';
                    $res['data']                =   $resp;
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function get_inventory_for_issued_return(){
        if($this->input->is_ajax_request()){
            $inventory  =   $this->input->post('inventory',true);
             $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
            if($inventory){
                $this->db->select("inv.id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("inventory_mgt as inv");
                $this->db->join("tbl_product_master as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory);
                $query = $this->db->get();
                if($query->num_rows()){
                    $data               =   $query->row();
                    $inventory_details  =   $query->row();
                    $total_grn          =   0;
                    $final_arr          =   array();
                    $final_arr_qty      =   array();
                    //===issued data===//
                    $total_issued       =   0;
                    $total_issued_for   =   array();
                    $this->db->where('inventory_id',$inventory);
                    //$this->db->group_by('inventory_id');
                    //$this->db->group_by('issued_return_for');
                    // $this->db->where('consume !=',1);
                   //$qry_issued = $this->db->select('SUM(qty) AS total_issued,MAX(issued_return_for) as issued_return_for,MAX(inventory_id) as inventory_id,SUM(consume_qty) as total_consume_qty')->get_where('issued_mgt', array('is_deleted' => '0'));
                   $qry_issued = $this->db->select('qty AS total_issued,issued_return_for,inventory_id,consume_qty as total_consume_qty')->get_where('issued_mgt', array('is_deleted' => '0'));
                    if($qry_issued->num_rows()){
                        foreach($qry_issued->result() as $isrw){
                            $total_issued  =   $isrw->total_issued ;
                            if(isset($total_issued_for[$isrw->issued_return_for])){
                                $smt_qty = $isrw->total_issued - $isrw->total_consume_qty;
                                $total_issued_for[$isrw->issued_return_for]=$total_issued_for[$isrw->issued_return_for] + $smt_qty;
                            }
                            else{
                                $total_issued_for[$isrw->issued_return_for]=$isrw->total_issued - $isrw->total_consume_qty;
                            }
                        }
                    }
                    // pr($total_issued);
                    //pr($total_issued_for); die;
                    //===issued data===//
                    if(isset($total_issued_for[0]) && !empty($total_issued_for[0]) && $total_issued_for[0]>0){
                        $final_arr['0']     =   'Opening Stock';
                        $final_arr_qty['0'] =   $total_issued_for[0];
                    }
                    //====GRN Details==//
                    //$this->db->group_by('po_number');
                   // $this->db->group_by('order_no');
                    //$qry_grn = $this->db->select('MAX(id) as id,SUM(grn_qty) as total_grn_quantity,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number')->get_where('grn_mgt', array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    $qry_grn = $this->db->select('id,grn_qty as total_grn_quantity,vendor_name,grn_type,order_no,order_id,inventory_id,po_number')->get_where('grn_mgt', array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    if($qry_grn->num_rows()){
                        $po_number = array();
                        $manual_po_number = array();
                        $grn_datas = $qry_grn->result();
                        foreach($qry_grn->result() as $key=> $val){
                            if($val->grn_type == 1){
                                $po_number[] = $val->po_number;
                            }
                            if($val->grn_type == 4){
                                $manual_po_number[] = $val->po_number;
                            }

                        }
                        //==Store Details==//
                        $get_store_name =   array();
                        if(isset($po_number) && !empty($po_number)){
                            $this->db->select('tro.po_number,trp.vend_user_id,ts.store_name');
                            $this->db->join('tbl_rfq_price as trp','trp.id =tro.price_id');
                            $this->db->join('tbl_users as tu','tu.id =trp.vend_user_id');
                            $this->db->join('tbl_store as ts','ts.store_id =tu.store_id');
                            $this->db->where_in('tro.po_number',$po_number);
                            $qry = $this->db->get('tbl_rfq_order tro');
                            if($qry->num_rows()){
                                foreach($qry->result() as $keys => $vals){
                                    $get_store_name[$vals->po_number] =$vals->store_name;
                                }
                            }
                        }
                        if(isset($manual_po_number) && !empty($manual_po_number)){
                            $this->db->select('mpo.manual_po_number,mpo.vendor_id as vend_user_id,ts.store_name');
                            $this->db->join('tbl_store as ts','ts.store_id =mpo.vendor_id');
                            $this->db->where_in('mpo.manual_po_number',$manual_po_number);
                            $qry = $this->db->get('tbl_manual_po_order mpo');
                            if($qry->num_rows()){
                                foreach($qry->result() as $keys => $vals){
                                    $get_store_name[$vals->manual_po_number] =$vals->store_name;
                                }
                            }
                        }
                        //==Store Details==//
                        if(isset($grn_datas) && !empty($grn_datas)){
                            foreach($grn_datas as $key => $vals){
                                if(isset($total_issued_for[$vals->id]) && !empty($total_issued_for[$vals->id])){
                                    if($vals->grn_type == 2){
                                        $final_arr[$vals->id] =  $vals->order_no.'/'.$vals->vendor_name;
                                    }else{
                                        $final_arr[$vals->id] =  $vals->po_number.'/'.$get_store_name[$vals->po_number];
                                    }
                                    $final_arr_qty[$vals->id] =  $total_issued_for[$vals->id];
                                }
                            }
                        }
                    }
                    //pr($grn_datas); die;
                    //====GRN Details==//
                    //===Issued Return mgt==//
                    // $this->db->group_by('inventory_id');
                    // $this->db->group_by('issued_return_for');
                    // $qry_return_issued = $this->db->select('SUM(qty) as total_issued_return ,MAX(issued_return_for) as issued_return_for,MAX(inventory_id) as inventory_id')->get_where('issued_return_mgt',array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    $qry_return_issued = $this->db->select('qty as total_issued_return ,issued_return_for,inventory_id')->get_where('issued_return_mgt',array('inventory_id' => $inventory, 'is_deleted' => '0'));
                    if($qry_return_issued->num_rows()){
                        foreach($qry_return_issued->result() as $keys => $value){
                            $final_arr_qty[$value->issued_return_for] = $final_arr_qty[$value->issued_return_for] - $value->total_issued_return;
                        }
                    }

                    //===Issued Return Mgt==//
                    $chk_array=array();
                    foreach($final_arr as $flkey => $flvals){
                        if(isset($chk_array[$flvals])){
                                $final_arr_qty[$flkey] = $final_arr_qty[$flkey]+$final_arr_qty[$chk_array[$flvals]];
                                unset($final_arr[$chk_array[$flvals]]);
                                unset($final_arr_qty[$chk_array[$flvals]]);
                        }
                        else{
                                $chk_array[$flvals]=$flkey;
                        }
                    }
                    //pr($final_arr); die;
                    //pr($final_arr_qty); die;
                    foreach($final_arr_qty as $fkey => $fvals){
                        if($fvals<=0){
                            unset($final_arr_qty[$fkey]);
                            unset($final_arr[$fkey]);
                        }
                    }
                    //pr($final_arr_qty); die;
                    if(isset($final_arr) && !empty($final_arr)){
                        $resp['my_default_stock']   =   isset($final_arr_qty[0]) ? $final_arr_qty[0] : '0';
                        $resp['inv_id']             =   $data->id;
                        $resp['added_qty']          =   isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                        $resp['branch_id']          =   $data->branch_id;
                        $resp['comp_br_sp_inv_id']  =   $data->comp_br_sp_inv_id;
                        $resp['product_id']         =   $data->product_id;
                        $resp['specification']      =   $data->specification;
                        $resp['size']               =   $data->size;
                        $resp['uom']                =   $data->uom;
                        $resp['product_name']       =   $data->prod_name;
                        $resp['uom_name']           =   $data->uom_name;

                        $res['status']              =   1;
                        $res['return_for']          =   $final_arr;
                        $res['return_for_qty']      =   $final_arr_qty;
                        $res['message']             =   'Inventory found';
                        $res['data']                =   $resp;
                        echo json_encode($res); die;
                    }
                    else{
                        $res['status']          =   2;
                        $res['message']         =   'Error, No issue Found';
                        echo json_encode($res); die;
                    }
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Inventory not found';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function add_issued_return_data(){
        if($this->input->is_ajax_request()){
            $insert_data        =   array();
            $max_issued_return  =   1;
            $inventory_id       =   $this->input->post('inven_id',true);
            $branch_id          =   $this->input->post('branch_id',true);
            $remark             =   $this->input->post('remark',true);
            $qty                =   $this->input->post('qty',true);
            $qty                =   $this->input->post('qty',true);
            // $vendor_name  =   $this->input->post('vendor_name',true);
            $current_stock      =   $this->input->post('current_stock',true);
            $issued_return_for  =   $this->input->post('issued_return_for',true);
            $issued_return_type =   $this->input->post('issued_return_type',true);
            if($current_stock < $qty){
                $res['status']          =   2;
                $res['message']         =   'Quantity Should be less than Current Stock';
                 echo json_encode($res); die;
            }
            if($inventory_id){
                $users          =   $this->session->userdata('auth_user');
                if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
                $vrify_qry = $this->db->select_max("issued_return_no")->get_where('issued_return_mgt',array('company_id' => $company_id));
                if($vrify_qry->num_rows()){
                    $row_data               =   $vrify_qry->row();
                    $max_issued_return                 =   ($row_data->issued_return_no)+1;
                }
                $insert_data['company_id']          =   $company_id;
                $insert_data['inventory_id']        =   $inventory_id;
                $insert_data['branch_id']           =   $branch_id;
                $insert_data['issued_return_no']    =   $max_issued_return;
                $insert_data['qty']                 =   $qty;
                $insert_data['vendor_name']         =   '';
                $insert_data['remark']              =   $remark;
                $insert_data['issued_return_for']   =   $issued_return_for;
                $insert_data['issued_return_type']  =   isset($issued_return_type) && $issued_return_type>=1 ? $issued_return_type : 0;
                $insert_data['last_updated_by']     =   $users['users_id'];
                $insert_data['last_updated_date']   =   date('Y-m-d H:i:s');

                $qry = $this->db->insert('issued_return_mgt',$insert_data);
                if($qry){
                    $res['status']          =   1;
                    $res['message']         =   'Issued Return added successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Issued Return not added, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }

    }
    public function add_return_stock_data(){
        if($this->input->is_ajax_request()){
            $insert_data            =   array();
            $max_stock              =   1;
            $inventory_id           =   $this->input->post('inven_id',true);
            $branch_id              =   $this->input->post('branch_id',true);
            $remark                 =   $this->input->post('remark',true);
            $qty                    =   $this->input->post('qty',true);
            // $vendor_name         =   $this->input->post('vendor_name',true);
            $current_stock          =   $this->input->post('current_stock',true);
            $stock_return_for       =   $this->input->post('stock_return_for',true);
            $stock_vendor_name      =   $this->input->post('stock_vendor_name',true);
            $stock_vehicle_no_lr_no =   $this->input->post('stock_vehicle_no_lr_no',true);
            $stock_debit_note_no    =   $this->input->post('stock_debit_note_no',true);
            $stock_frieght          =   $this->input->post('stock_frieght',true);
            $stock_return_type      =   $this->input->post('stock_return_type',true);
            if($current_stock < $qty){
                $res['status']          =   2;
                $res['message']         =   'Quantity Should be less than Current Stock';
                 echo json_encode($res); die;
            }
            if($qty == '0'){
                $res['status']          =   2;
                $res['message']         =   'QTY should be greater Than zero!';
                 echo json_encode($res); die;
            }
            if($inventory_id){
                $users          =   $this->session->userdata('auth_user');
                if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
                $vrify_qry = $this->db->select_max("stock_no")->get_where('tbl_return_stock',array('company_id' => $company_id));
                if($vrify_qry->num_rows()){
                    $row_data               =   $vrify_qry->row();
                    $max_stock              =   ($row_data->stock_no)+1;
                }
                $insert_data['company_id']              =   $company_id;
                $insert_data['inventory_id']            =   $inventory_id;
                $insert_data['branch_id']               =   $branch_id;
                $insert_data['stock_no']                =   $max_stock;
                $insert_data['qty']                     =   $qty;
                $insert_data['vendor_name']             =   '';
                $insert_data['remark']                  =   $remark;
                $insert_data['stock_return_for']        =   $stock_return_for;
                $insert_data['stock_vendor_name']       =   $stock_vendor_name;
                $insert_data['stock_vehicle_no_lr_no']  =   $stock_vehicle_no_lr_no;
                $insert_data['stock_debit_note_no']     =   $stock_debit_note_no;
                $insert_data['stock_frieght']           =   $stock_frieght;
                $insert_data['stock_return_type']       =   $stock_return_type;
                $insert_data['last_updated_by']         =   $users['users_id'];
                $insert_data['last_updated_date']       =   date('Y-m-d H:i:s');
                $qry = $this->db->insert('tbl_return_stock',$insert_data);
                if($qry){
                    $res['status']          =   1;
                    $res['message']         =   'Stock Return added successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Stock Return not added, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }

    }
    public function get_inventory_for_return_stock_edit(){
        if($this->input->is_ajax_request()){
            $stock_id  =   $this->input->post('stock_id',true);
            $inventory_id  =   $this->input->post('inventory_id',true);
             $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
                //pr($_POST);die;
            if($inventory_id){
                $this->db->select("sr.*,inv.id,sr.id stock_id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.prod_name product_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("tbl_return_stock as sr");
                $this->db->join("inventory_mgt as inv",'sr.inventory_id=inv.id');
                $this->db->join("view_live_master_product_with_alias as tp",'tp.prod_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory_id);
                $this->db->where('sr.id',$stock_id);
                $this->db->where('sr.is_deleted','0');
                $query = $this->db->get();
                if($query->num_rows()){
                    $data   =   $query->row();
                    $inventory_details =  $query->row();
                    $stock_returns_for = $query->row()->stock_return_for;

                    /// get stock return total qty //////
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('stock_return_for,inventory_id');
                    $this->db->where('is_deleted','0');
                    // $this->db->where('id !=',$stock_id);
                    $this->db->where('stock_return_for ',$stock_returns_for);
                    $qry_tot_stock_env = $this->db->select('SUM(qty) AS total_stock_quantity')->get_where('tbl_return_stock');
                    if($qry_tot_stock_env->num_rows()){
                        // echo $this->db->last_query();die;
                        $total_stock_result    =   $qry_tot_stock_env->row()->total_stock_quantity;
                    }
                    // pr($total_stock_result);die;
                    ///  get perticular grn data //////
                    if($stock_returns_for > 0){
                       $this->db->where('inventory_id', $inventory_id);
                       $this->db->where('is_deleted','0');
                        $this->db->group_start();
                        $this->db->where('id', $stock_returns_for);
                        $this->db->or_where("(`stock_return_for` = '$stock_returns_for')");
                        $this->db->group_end();

                        $p_grn_qry = $this->db->select('id,po_number,grn_qty')->get_where('grn_mgt');
                        // echo $this->db->last_query();die;
                        $grn_ids = array();
                        $po_number = '';
                        $grn_new_qty = 0;
                        if($p_grn_qry->num_rows()){
                            foreach($p_grn_qry->result() as $vals){
                                $grn_ids[] = $vals->id;
                                if(isset($vals->po_number) && !empty($vals->po_number)){

                                $po_number = $vals->po_number;
                                }
                                $grn_new_qty +=  $vals->grn_qty;

                            }
                            // pr($grn_new_qty);die;
                            $this->db->where('inventory_id', $inventory_id);
                            $this->db->where('po_number', $po_number);
                            $this->db->where('is_deleted','0');
                            $this->db->where_not_in('id', $grn_ids);
                            $p_grn_qry_new = $this->db->select('id,grn_qty')->get_where('grn_mgt');
                                 if($p_grn_qry_new->num_rows()){
                                    foreach($p_grn_qry_new->result() as $val){
                                    $grn_ids[] = $vals->id;
                                    $grn_new_qty +=  $val->grn_qty;

                                }
                             }
                            $total_p_grn_result    =  $grn_new_qty;
                        }
                            // pr($total_p_grn_result);die;
                    }

                    $total_grn  =   0;

                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt');
                    if($qry_tot_grn_env->num_rows()){
                        $total_grn    =   $qry_tot_grn_env->row()->total_grn_quantity;
                    }
                    // pr($grn_ids );die;
                    $total_issued  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->where('is_deleted',0);
                    $this->db->where_in('issued_return_for', $grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issued_env = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt');
                    if($qry_issued_env->num_rows()){
                        $total_issued  =   $qry_issued_env->row()->total_issued;
                        // echo $this->db->last_query();die;
                    }
                     $total_issued_return  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->where('is_deleted',0);
                    $this->db->where_in('issued_return_for', $grn_ids);
                    $this->db->group_by('inventory_id');
                    $qry_issued_return_env = $this->db->select('SUM(qty) AS total_issued_return')->get_where('issued_return_mgt');
                    if($qry_issued_return_env->num_rows()){

                        // pr($qry_issued_return_env->row()->total_issued_return);die;
                        $total_issued_return  =   $qry_issued_return_env->row()->total_issued_return;
                       // echo  $this->db->last_query();die;
                    }

                    $resp    =   array();
                        // echo $total_p_grn_result.'=='.$total_stock_result.'=='.$total_issued.'=='.$total_issued_return;die;
                     if($stock_returns_for == 0){
                        // echo $inventory_details->opening_stock.'=='.$total_stock_result;die;
                        $abc    =  ($inventory_details->opening_stock - $total_stock_result ) -  ($total_issued);
                     }else{
                         $abc     = ($total_p_grn_result - $total_stock_result + $total_issued_return ) - ($total_issued);
                     }
                    // $resp['current_stock']     =   ($inventory_details->opening_stock + $total_grn) - ($total_issued);
                    // echo "op-> ".$inventory_details->opening_stock."t_grn -> ".$total_grn."t_issued-> ".$total_issued;die;
                    $qry_tot_qty_rs = $this->db->select('SUM(qty) AS added_qty')->get_where('tbl_return_stock',['inventory_id'=>$data->inventory_id,'company_id'=>$data->company_id,'is_deleted'=>'0','branch_id'=>$data->branch_id,'id !='=>$data->stock_id]);
                    //echo $this->db->last_query();die;
                    if($qry_tot_qty_rs->num_rows()){
                        $rs_added_qty    =   $qry_tot_qty_rs->row()->added_qty;
                    }
                    //pr($rs_added_qty);die('rs_added_');
                    $resp['inv_id']              =   $data->id;
                    $resp['stock_id']              =   $data->stock_id;
                    // $resp['added_qty']           =  isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                    $resp['added_qty']                  =  isset($abc) && !empty($abc)?$abc: '0';
                    $resp['branch_id']                  =   $data->branch_id;
                    $resp['comp_br_sp_inv_id']          =   $data->comp_br_sp_inv_id;
                    $resp['product_id']                 =   $data->product_id;
                    $resp['specification']              =   $data->specification;
                    $resp['size']                       =   $data->size;
                    $resp['uom']                        =   $data->uom;
                    $resp['product_name']               =   $data->product_name;
                    $resp['uom_name']                   =   $data->uom_name;
                    $resp['vendor_name']                =   $data->vendor_name;
                    $resp['remark']                     =   $data->remark;
                    $resp['stock_vendor_name']          =   $data->stock_vendor_name;
                    $resp['stock_vehicle_no_lr_no']     =   $data->stock_vehicle_no_lr_no;
                    $resp['stock_debit_note_no']        =   $data->stock_debit_note_no;
                    $resp['stock_frieght']              =   $data->stock_frieght;
                    $resp['qty']                        =   $data->qty;

                    $res['status']              =   1;
                    $res['message']             =   'Inventory found';
                    $res['data']                =   $resp;
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Stock Return is  Deleted';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function get_inventory_for_return_stock_edit_old(){
        if($this->input->is_ajax_request()){
            $stock_id  =   $this->input->post('stock_id',true);
            $inventory_id  =   $this->input->post('inventory_id',true);
             $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
                //pr($_POST);die;
            if($inventory_id){
                $this->db->select("sr.*,inv.id,sr.id stock_id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.product_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("tbl_return_stock as sr");
                $this->db->join("inventory_mgt as inv",'sr.inventory_id=inv.id');
                $this->db->join("tbl_product as tp",'tp.product_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory_id);
                $this->db->where('sr.id',$stock_id);
                $this->db->where('sr.is_deleted','0');
                $query = $this->db->get();
                if($query->num_rows()){
                    $data   =   $query->row();
                    $inventory_details =  $query->row();
                    $total_grn  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt');
                    if($qry_tot_grn_env->num_rows()){
                        $total_grn    =   $qry_tot_grn_env->row()->total_grn_quantity;
                    }

                    $total_issued  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->where('is_deleted',0);
                    $this->db->group_by('inventory_id');
                    $qry_issued_env = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt');
                    if($qry_issued_env->num_rows()){
                        $total_issued  =   $qry_issued_env->row()->total_issued;
                    }

                    $resp    =   array();
                    $resp['current_stock']     =   ($inventory_details->opening_stock + $total_grn) - ($total_issued);
                    $qry_tot_qty_rs = $this->db->select('SUM(qty) AS added_qty')->get_where('tbl_return_stock',['inventory_id'=>$data->inventory_id,'company_id'=>$data->company_id,'is_deleted'=>'0','branch_id'=>$data->branch_id,'id !='=>$data->stock_id]);
                    //echo $this->db->last_query();die;
                    if($qry_tot_qty_rs->num_rows()){
                        $rs_added_qty    =   $qry_tot_qty_rs->row()->added_qty;
                    }
                    //pr($rs_added_qty);die('rs_added_');
                    $resp['inv_id']              =   $data->id;
                    $resp['stock_id']              =   $data->stock_id;
                    $resp['added_qty']           =  isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                    $resp['branch_id']           =   $data->branch_id;
                    $resp['comp_br_sp_inv_id']   =   $data->comp_br_sp_inv_id;
                    $resp['product_id']          =   $data->product_id;
                    $resp['specification']       =   $data->specification;
                    $resp['size']                =   $data->size;
                    $resp['uom']                 =   $data->uom;
                    $resp['product_name']        =   $data->product_name;
                    $resp['uom_name']            =   $data->uom_name;
                    $resp['vendor_name']            =   $data->vendor_name;
                    $resp['remark']            =   $data->remark;
                    $resp['qty']            =   $data->qty;

                    $res['status']              =   1;
                    $res['message']             =   'Inventory found';
                    $res['data']                =   $resp;
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error, Stock Return is  Deleted';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
     public function get_inventory_for_issued_return_edit(){
        if($this->input->is_ajax_request()){
                // pr($_POST);die;
            $issued_return_id  =   $this->input->post('issued_return_id',true);
            $inventory_id  =   $this->input->post('inventory_id',true);
             $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                    $company_id  =   $users['parent_id'];
                } else {
                    $company_id   =  $users['users_id'];
                }
            if($inventory_id){
                $this->db->select("ir.*,ir.id issued_return_id, inv.id,ir.id stock_id,inv.comp_br_sp_inv_id,inv.product_id,inv.specification,inv.size,inv.uom,tp.product_name,tu.uom_name,inv.opening_stock,inv.branch_id", false);
                $this->db->from("issued_return_mgt as ir");
                $this->db->join("inventory_mgt as inv",'ir.inventory_id=inv.id');
                $this->db->join("tbl_product as tp",'tp.product_id=inv.product_id', 'LEFT');
                $this->db->join("tbl_uom as tu",'tu.id=inv.uom', 'LEFT');
                $this->db->where('inv.id',$inventory_id);
                $this->db->where('ir.id',$issued_return_id);
                $this->db->where('ir.is_deleted','0');
                $query = $this->db->get();
                if($query->num_rows()){
                    $data   =   $query->row();
                    // pr($data);die;
                    $inventory_details =  $query->row();
                    $total_grn  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $qry_tot_grn_env = $this->db->select('SUM(grn_qty) AS total_grn_quantity')->get_where('grn_mgt');
                    if($qry_tot_grn_env->num_rows()){
                        $total_grn    =   $qry_tot_grn_env->row()->total_grn_quantity;
                    }

                    $total_issued  =   0;
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->where('is_deleted',0);
                    $this->db->group_by('inventory_id');
                    $qry_issued_env = $this->db->select('SUM(qty) AS total_issued')->get_where('issued_mgt');
                    if($qry_issued_env->num_rows()){
                        $total_issued  =   $qry_issued_env->row()->total_issued;
                    }
                    $final_arr = array();
                   $final_arr_qty = array();
                   $all_issued_qty = array();
                   $this->db->select('SUM(qty) as total_issued_qty ,MAX(inventory_id) as inventory_id,MAX(issued_return_for) as issued_return_for');
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_total_issued_qty = $this->db->get('issued_mgt');

                    if($qry_total_issued_qty->num_rows()){
                        foreach($qry_total_issued_qty->result() as $keys =>$vals){
                            $all_issued_qty[$vals->issued_return_for] = $vals->total_issued_qty;
                        }
                    }
                   // pr($all_issued_qty);
                    $all_return_qty = array();
                   $this->db->select('SUM(qty) as total_issued_qty ,MAX(inventory_id) as inventory_id,MAX(stock_return_for) as stock_return_for');
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('stock_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_total_return_qty = $this->db->get('tbl_return_stock');
                    if($qry_total_return_qty->num_rows()){
                        foreach($qry_total_return_qty->result() as $keys =>$vals){
                             $all_return_qty[$vals->stock_return_for] = $vals->total_issued_qty;
                        }
                    }
                    // pr($all_return_qty);
                    if(isset($data->opening_stock) && !empty($data->opening_stock ) && $data->opening_stock != '0'){

                       $final_arr['0'] = 'Opening Stock';
                       $final_arr_qty['0'] = $all_issued_qty[0] + $all_return_qty[0];
                   }
                   $this->db->select('SUM(grn_qty) as total_grn_quantity , MAX(id) as id,MAX(vendor_name) as vendor_name,MAX(grn_type) as grn_type,MAX(order_no) as order_no,MAX(order_id) as order_id,MAX(inventory_id) as inventory_id,MAX(po_number) as po_number');
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('po_number');
                    $this->db->where('is_deleted',0);
                     $qry_grn = $this->db->get('grn_mgt gm');
                    if($qry_grn->num_rows()){
                        $po_number = array();
                        $grn_datas = $qry_grn->result();
                        foreach($grn_datas as $key => $vals){
                            $final_arr_qty[$vals->id] =  ($all_return_qty[$vals->id]) + ($all_issued_qty[$vals->id]);
                        }
                    }
                    // pr($final_arr_qty);
                    $this->db->select('SUM(qty) as total_grn_quantity ,MAX(issued_return_for) as issued_return_for,MAX(inventory_id) as inventory_id');
                    $this->db->where('inventory_id',$inventory_id);
                    $this->db->where('id',$issued_return_id );
                    $this->db->group_by('inventory_id');
                    $this->db->group_by('issued_return_for');
                    $this->db->where('is_deleted',0);
                     $qry_return_issued = $this->db->get('issued_return_mgt');
                    if($qry_return_issued->num_rows()){
                        // pr($qry_return_issued->result());
                         foreach($qry_return_issued->result() as $keys => $value){
                            $final_arr_qty[$value->issued_return_for] = $final_arr_qty[$value->issued_return_for] - $value->total_grn_quantity;
                         }
                    }
                    // pr($final_arr_qty);die;
                    $resp    =   array();
                    $resp['current_stock2']     =  isset($final_arr_qty[$data->issued_return_for]) ? $final_arr_qty[$data->issued_return_for] :'0';
                    $resp['current_stock']     =   ($inventory_details->opening_stock + $total_grn) - ($total_issued);
                    $qry_tot_qty_rs = $this->db->select('SUM(qty) AS added_qty')->get_where('issued_return_mgt',['inventory_id'=>$data->inventory_id,'company_id'=>$data->company_id,'is_deleted'=>'0','branch_id'=>$data->branch_id,'id !='=>$data->issued_return_id]);
                    //echo $this->db->last_query();die;
                    if($qry_tot_qty_rs->num_rows()){
                        $rs_added_qty    =   $qry_tot_qty_rs->row()->added_qty;
                    }
                    //pr($rs_added_qty);die('rs_added_');
                    $resp['inv_id']              =   $data->id;
                    $resp['issued_return_id']              =   $data->issued_return_id;
                    $resp['added_qty']           =  isset($rs_added_qty) && !empty($rs_added_qty)?$rs_added_qty: '0';
                    $resp['branch_id']           =   $data->branch_id;
                    $resp['comp_br_sp_inv_id']   =   $data->comp_br_sp_inv_id;
                    $resp['product_id']          =   $data->product_id;
                    $resp['specification']       =   $data->specification;
                    $resp['size']                =   $data->size;
                    $resp['uom']                 =   $data->uom;
                    $resp['product_name']        =   $data->product_name;
                    $resp['uom_name']            =   $data->uom_name;
                    $resp['vendor_name']            =   $data->vendor_name;
                    $resp['remark']            =   $data->remark;
                    $resp['qty']            =   $data->qty;

                    $res['status']              =   1;
                    $res['message']             =   'Inventory found';
                    $res['data']                =   $resp;
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Error,Issued Return is  Deleted';
                    echo json_encode($res); die;
                }
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Inventory not found';
                echo json_encode($res); die;
            }
        }
    }
    public function edit_return_stock_data(){
        if($this->input->is_ajax_request()){
            $updated        = array();
            $remark                     =   $this->input->post('remark',true);
            $stock_id                   =   $this->input->post('stock_id',true);
            //$qty                      =   $this->input->post('qty',true);
            $stock_vendor_name          =   $this->input->post('stock_vendor_name',true);
            $stock_vehicle_no_lr_no     =   $this->input->post('stock_vehicle_no_lr_no',true);
            $stock_debit_note_no        =   $this->input->post('stock_debit_note_no',true);
            $stock_frieght              =   $this->input->post('stock_frieght',true);
            // $current_stock           =   $this->input->post('current_stock',true);
            // if($current_stock < $qty){
            //     $res['status']          =   2;
            //     $res['message']         =   'Quantity Should be less than Current Stock';
            //      echo json_encode($res); die;
            // }
            if($stock_id){
                $users          =   $this->session->userdata('auth_user');
                //$updated['qty'] = $qty;
                $updated['stock_vendor_name']       =   $stock_vendor_name;
                $updated['remark']                  =   $remark;
                $updated['stock_vehicle_no_lr_no']  =   $stock_vehicle_no_lr_no;
                $updated['stock_debit_note_no']     =   $stock_debit_note_no;
                $updated['stock_frieght']           =   $stock_frieght;
                $updated['last_updated_by']         =   $users['users_id'];
                $updated['last_updated_date']       =   date('Y-m-d H:i:s');
                $this->db->where('id',$stock_id);
                $qry = $this->db->update('tbl_return_stock',$updated);
                if($qry){
                    $res['status']          =   1;
                    $res['message']         =   'Stock Return Updated successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Stock Return not Updated, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Stock Return not found';
                echo json_encode($res); die;
            }
        }

    }
    public function edit_issued_return_data(){
        if($this->input->is_ajax_request()){
            $updated = array();
            $remark  =   $this->input->post('remark',true);
            $issued_return_id  =   $this->input->post('issued_return_id',true);
            $qty  =   $this->input->post('qty',true);
            $vendor_name  =   $this->input->post('vendor_name',true);
            $current_stock  =   $this->input->post('current_stock',true);
            if($current_stock < $qty){
                $res['status']          =   2;
                $res['message']         =   'Quantity Should be less than Current Stock';
                 echo json_encode($res); die;
            }
            if($issued_return_id){
                $users          =   $this->session->userdata('auth_user');
                $updated['qty'] = $qty;
                $updated['vendor_name'] = $vendor_name;
                $updated['remark'] = $remark;
                $updated['last_updated_by'] = $users['users_id'];
                $updated['last_updated_date'] = date('Y-m-d H:i:s');
                $this->db->where('id',$issued_return_id);
                // pr($_POST);
                // pr($updated);die;
                $qry = $this->db->update('issued_return_mgt',$updated);
                if($qry){
                    $res['status']          =   1;
                    $res['message']         =   'Issued Return Updated successfully';
                    echo json_encode($res); die;
                }
                else{
                    $res['status']          =   2;
                    $res['message']         =   'Issued Return not Updated, please try again';
                    echo json_encode($res); die;
                }

            }
            else{
                $res['status']          =   2;
                $res['message']         =   'Error, Issued Return not found';
                echo json_encode($res); die;
            }
        }

    }
      public function delete_stock_return(){
        //===inventory===//
        //$inven_id = $_POST['inven_id'];
        $this->db->where('id',$_POST['stock_id']);
        $res = $this->db->update('tbl_return_stock',['is_deleted'=>1]);
        if($res){
            $response['status']     =   '1';
            $response['message']    =   'Stock Return Deleted successfully';
        }else{
             $response['status']     =   '2';
            $response['message']    =   'SomeThing Went Wrong Try Again!';
        }
        echo json_encode($response);die;

    }
    public function delete_issued_return(){
        //===inventory===//
        //$inven_id = $_POST['inven_id'];
        $this->db->where('id',$_POST['issued_return_id']);
        $res = $this->db->update('issued_return_mgt',['is_deleted'=>1]);
        if($res){
            $response['status']     =   '1';
            $response['message']    =   'Stock Return Deleted successfully';
        }else{
             $response['status']     =   '2';
            $response['message']    =   'SomeThing Went Wrong Try Again!';
        }
        echo json_encode($response);die;

    }
    public function save_grn_data_without_po(){
        if($this->input->is_ajax_request()){
            $inserted                   =   array();
            $order_no                   =   $this->input->post('order_no',true);
            $rfq_no                     =   $this->input->post('rfq_no',true);
            $order_qty                  =   $this->input->post('order_qty',true);
            $rate                       =   $this->input->post('rate',true);
            $vendor_name                =   $this->input->post('vendor_name',true);
            $grn_qty                    =   $this->input->post('grn_qty',true);
            $approved_by                =   $this->input->post('approved_by',true);
            $inventory_id               =   $this->input->post('inventory',true);
            $vendor_invoice_number      =   $this->input->post('vendor_invoice_number',true);
            $vehicle_no_lr_no           =   $this->input->post('vehicle_no_lr_no',true);
            $gross_wt                   =   $this->input->post('gross_wt',true);
            $gst_no                     =   $this->input->post('gst_no',true);
            $frieght_other_charges      =   $this->input->post('frieght_other_charges',true);
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $company_id  =   $users['parent_id'];
            } else {
                $company_id   =  $users['users_id'];
            }
            $grn_no     =   1;
            $vrify_qry = $this->db->select_max("grn_no")->get_where('grn_mgt',array('company_id' => $company_id));
            if($vrify_qry->num_rows()){
                $row_data               =   $vrify_qry->row();
                $grn_no                 =   ($row_data->grn_no)+1;
            }
            $inserted['grn_qty']                    =   $grn_qty;
            $inserted['vendor_name']                =   $vendor_name;
            $inserted['order_no']                   =   $order_no;
            $inserted['po_number']                  =   $order_no;
            $inserted['rate']                       =   $rate;
            $inserted['rfq_no']                     =   $rfq_no;
            $inserted['order_qty']                  =   $order_qty;
            $inserted['approved_by']                =   $approved_by;
            $inserted['grn_type']                   =   2;
            $inserted['company_id']                 =   $company_id;
            $inserted['grn_no']                     =   $grn_no;
            $inserted['vendor_invoice_number']      =   $vendor_invoice_number;
            $inserted['vehicle_no_lr_no']           =   $vehicle_no_lr_no;
            $inserted['gross_wt']                   =   $gross_wt;
            $inserted['gst_no']                     =   $gst_no;
            $inserted['frieght_other_charges']      =   $frieght_other_charges;
            $inserted['inventory_id']               =   $inventory_id;
            $inserted['last_updated_by']            =   $users['users_id'];
            $inserted['last_updated_date']          =   date('Y-m-d H:i:s');
            $qry = $this->db->insert('grn_mgt',$inserted);
            if($qry){
                $res['status']          =   1;
                $res['message']         =   'GRN Inserted successfully';
                echo json_encode($res); die;
            }
            else{
                $res['status']          =   2;
                $res['message']         =   'GRN Not Inserted, please try again';
                echo json_encode($res); die;
            }
        }
    }

    public function indent_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Indent Report";
        //$user_id                  =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch   =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $this->load->view('inventory_management/indent_list',$data);
    }

    public function get_indent_report_data()
    {
        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_indent_report_data($users_ids, $buyer_users, 'page',$cat_id);
        $total_record = $this->inventory_management_model->get_indent_report_data($users_ids, $buyer_users, 'total',$cat_id);
        //pr($result); die;
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $inven_data = [];
        $data1 = array();
        if (isset($result) && !empty($result)) {
            foreach ($result as $key => $val) {
                $sub_array = array();
                //listing------
                $sub_array[] = $val->comp_br_sp_ind_id;
                $sub_array[] = $val->prod_name;
                $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
                $sub_array[] = strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
                $sub_array[] = strlen($val->inventory_grouping) <=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
                $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
                if ($val->is_deleted) {
                    $sub_array[] = round($val->indent_qty,2) . '(Deleted)';
                } else {
                    $sub_array[] = round($val->indent_qty,2);
                }
                $sub_array[] = $val->uom_name;
                $sub_array[] = strlen($val->remarks)<=20 ? $val->remarks : substr($val->remarks,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->remarks.'"></i>';
                if($val->is_active==1){
                    $sub_array[] = 'Approved';
                }
                else{
                    $sub_array[] = 'Unapproved';
                }
                $sub_array[] = date("d/m/Y", strtotime($val->last_updated_date));


                $data1[] = $sub_array;
            }
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function indent_report_export(){
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_indent_report_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record = $this->inventory_management_model->get_indent_report_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $inven_data = [];
        $data1 = array();
        if (isset($result) && !empty($result)) {
            foreach ($result as $key => $val) {
                $sub_array = array();
                //listing------
                $sub_array[] = $val->comp_br_sp_ind_id;
                $sub_array[] = $val->prod_name;
                $sub_array[] = ($val->specification);
                $sub_array[] = ($val->size);
                $sub_array[] = ($val->inventory_grouping);
                $sub_array[] = $val->first_name; //$val->first_name . ' ' . $val->last_name;
                if ($val->is_deleted) {
                    $sub_array[] = round($val->indent_qty,2) . '(Deleted)';
                } else {
                    $sub_array[] = round($val->indent_qty,2);
                }
                $sub_array[] = $val->uom_name;
                $sub_array[] = HtmlDecodeString($val->remarks);
                $sub_array[] = isset($val->is_active) && $val->is_active==1 ? "Approved" : "Unapproved";
                $sub_array[] = date("d/m/Y", strtotime($val->last_updated_date));


                $data1[] = $sub_array;
            }
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function add_location(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $users = $this->session->userdata('auth_user');
            if ($users['parent_id'] != '') {
                $users_id = $users['parent_id'];
            } else {
                $users_id = $users['users_id'];
            }
            $location_name   =   $this->input->post('location_name');
            $buyer_branch   =   $this->input->post('buyer_branch');
            $this->db->where('name',$location_name);
            $this->db->where('buyer_user_id',$users_id);
            $this->db->where('buyer_branch',$buyer_branch);
            $select_qry = $this->db->get('location_mgt');
            if($select_qry->num_rows()){
                $response['status']     =   '0';
                $response['message']    =   'Location Already Added Selected Branch!';
            }else{
                if(isset($location_name) && !empty($location_name) && isset($buyer_branch) && !empty($buyer_branch)){
                    $insert['name']             =     $location_name;
                    $insert['buyer_user_id']    =     $users_id;
                    $insert['buyer_branch']     =     $buyer_branch;
                    $insert['updated_at']       =     date("Y-m-d H:i:s");
                    $insert['created_at']       =     date("Y-m-d H:i:s");
                    $qry = $this->db->insert('location_mgt',$insert);
                    if($qry){
                        $this->db->select('id,name');
                        $this->db->where('buyer_branch',$buyer_branch);
                        $this->db->where('buyer_user_id',$users_id);
                        $get_qry = $this->db->get('location_mgt');
                        if($get_qry->num_rows()){
                            $response['locations'] = $get_qry->result();
                        }else{
                            $response['locations']     =   array();
                        }
                        $response['status']     =   '1';
                        $response['message']    =   'Location Inserted Successfully';
                    }
                    else{
                        $response['status']     =   '0';
                        $response['message']    =   'please try again letter';
                    }
                }
            }

        }
        echo json_encode($response); die;
    }

    public function get_locations(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $users = $this->session->userdata('auth_user');
            if ($users['parent_id'] != '') {
                $users_id = $users['parent_id'];
            } else {
                $users_id = $users['users_id'];
            }
            $buyer_branch   =   $this->input->post('buyer_branch');
            $this->db->select('id,name');
            $this->db->where('buyer_user_id',$users_id);
            $this->db->where('buyer_branch',$buyer_branch);
            $select_qry = $this->db->get('location_mgt');
            if($select_qry->num_rows()){
                $response['status']     =   '1';
                $response['locations']     =   $select_qry->result();
                $response['message']    =   'get Locations successfully';
            }else{
                $response['status']     =   '0';
                $response['locations']     =   array();
                $response['message']    =   'Location Not Found!';
            }


        }
        echo json_encode($response); die;
    }

    public function get_consume_data(){
        if($this->input->is_ajax_request()){
            $response = array();
            $issued_id    =   $this->input->post('issued_id');
            if($issued_id){
                $qry = $this->db->select('inventory_id,issued_return_for')->get_where('issued_mgt',array('id' => $issued_id));
                if($qry->num_rows()){
                    $issued_data        =   $qry->row();
                    $inventory_id       =   $issued_data->inventory_id;
                    $issued_return_for  =   $issued_data->issued_return_for;
                    //===get total issued return==//
                    $issue_retn_qry     =   $this->db->select('qty')->get_where('issued_return_mgt',array('inventory_id' => $inventory_id, 'issued_return_for' => $issued_return_for));
                    $tot_issued_return  =   0;
                    if($issue_retn_qry->num_rows()){
                        foreach($issue_retn_qry->result() as $ir_rows){
                            $tot_issued_return = $tot_issued_return+$ir_rows->qty;
                        }
                    }

                    //===get total issued return==//
                    $this->db->order_by('id','Asc');
                    $issue_qry = $this->db->select('id,qty,consume_qty')->get_where('issued_mgt',array('inventory_id' => $inventory_id, 'issued_return_for' => $issued_return_for));
                    if($issue_qry->num_rows()){
                        foreach($issue_qry->result() as $iss_rws){
                            $isqty = $iss_rws->qty-$iss_rws->consume_qty;
                            if($isqty>=$tot_issued_return){
                                $max_allow_qty = $isqty-$tot_issued_return;
                                $tot_issued_return = 0;
                            }
                            else{
                                $tot_issued_return = $tot_issued_return-$isqty;
                                $max_allow_qty = 0;
                            }
                            if($iss_rws->id==$issued_id){
                                $consume_qty = $iss_rws->consume_qty;
                                break;
                            }
                        }
                    }
                    $response['status']         =   '1';
                    $response['max_allow_qty']  =   $max_allow_qty;
                    $response['consume_qty']    =   $consume_qty;
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'No data Found!';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'No data Found!';
            }
            echo json_encode($response); die;
        }
    }

    public function save_consume_data(){
        $response = array();
        if($this->input->is_ajax_request()){
            $issued_id          =   $this->input->post('issued_id');
            $fetch_consume_qty  =   $this->input->post('consume_qty');
            $consume_qry = $this->db->select('consume_qty')->get_where('issued_mgt',array('id' => $issued_id));
            if($consume_qry->num_rows()){
                $consume_qty    =   $consume_qry->row()->consume_qty;
                $total_consume  =   $consume_qty+$fetch_consume_qty;
                $upd                =   array();
                $upd['consume_qty'] =   $total_consume;
                $this->db->where('id',$issued_id);
                $upd_qry = $this->db->update('issued_mgt',$upd);
                if($upd_qry){
                    $response['status']     =   '1';
                    $response['message']    =   'Consume Qty Updated successfully';
                }
                else{
                    $response['status']     =   '1';
                    $response['message']    =   'Consume Qty Not Updated';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'No data Found!';
            }
        }
        echo json_encode($response); die;
    }

    public function stock_ledger(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Stock Ledger";
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['locations']          =   $this->inventory_management_model->get_locations($user_id);
        $data['uom_list']           =   getUOMList();
        $data['inventory_type']     =   GetInventoryType();
        $data['issued_types']       =   GetIssuedTypes();
        $this->load->view('inventory_management/stock_ledger',$data);
    }

    public function get_stock_ledger_data()
    {
        $cat_id=array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_stock_ledger_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_stock_ledger_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs    =   array();

        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            //===Total Indent Qty ===//
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));
            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                }
            }
            //===Total Indent Qty ===//

        }

        $data1      =   [];
        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){

                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();

            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }

            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//
            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN MANUAL PO====//
            $grn_manual_po_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_manual_po = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '4', 'is_deleted' => '0'));
            if($qry_grn_manual_po->num_rows()){
                foreach($qry_grn_manual_po->result() as $grn_manual_po_res){
                    $grn_manual_po_arr[$grn_manual_po_res->inventory_id]    =   $grn_manual_po_res->total_grn_quantity;
                }
            }
            //===GRN MANUAL PO====//
            //===GRN WPO====//
            $grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//
            $grn_wopo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->total_grn_quantity;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->total_grn_quantity;
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    $issued_arr[$issue_res->inventory_id]    =   $issue_res->total_issued_quantity;
                }
            }
            //===Issued===//

            //====Issued Return===//
            $issued_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->total_ir_quantity;
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(inventory_id) as inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->total_stock_return;
                }
            }
            //===Stock Return=====//
        }
            // pr($new_grn_wpo_arr);
            // pr($grn_qty);die;
        // pr($result);

        foreach ($result as $key => $val) {
            //===Indent Qty==//
           $total_quantity = isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            ///   new  grn /////
            $new_grn_qty = 0;
            if(isset($new_grn_wpo_arr[$val->id])){
                $new_grn_qty = $new_grn_wpo_arr[$val->id];
            }
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }
            //manual po
            $grn_qty_manual_po = 0;
            if(isset($grn_manual_po_arr[$val->id])){
                $grn_qty_manual_po = $grn_manual_po_arr[$val->id];
            }
            //manual po
            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock    =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty+$grn_qty_manual_po)-($issued_qty+$stock_return_qty);
            $is_del_invs     =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="1">';
            if($val->opening_stock>=1 || $val->is_indent==1){
                $is_del_invs =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="0">';
            }
           //$sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$val->id.'"  class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="close_indent_tds('.$val->id.')"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer" id="plus_'.$val->id.'" class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="open_indent_tds('.$val->id.')"><i class="bi bi-plus-lg"></i></span> <input type="checkbox" class="inventory_chkd" name="inv_checkbox" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'">';
            $sub_array[] = $key+1;
            //if(isset($val->indent_min_qty) && $val->indent_min_qty>0&& $mystock<=$val->indent_min_qty){
                //$sub_array[] = $val->product_name.'<button type="button" class="btn  position-relative" style="color:white !important; background: #015294 !important; border-color:#015294 !important;">Min Qty<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill " style="background:red!important; color: white !important; padding-left:2px!impotant;">'.$val->indent_min_qty.'</span></button>';
            // }
            // else{
                $sub_array[] = '<a href="'.base_url().'inventory_management/product_wise_stock_ledger/'.$val->id.'">'.$val->prod_name.'</a>';
            //}
            //$sub_array[] = $finldivcat['division_name'].'/'.$finldivcat['category_name'];
            //$sub_array[] = $finldivcat['category_name'];
            $sub_array[] = $val->cat_name;
            $sub_array[] = strlen($val->buyer_product_name)<=20 ? $val->buyer_product_name : substr($val->buyer_product_name,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->buyer_product_name.'"></i>';
            $sub_array[] = strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[] = strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>'.$is_del_invs;
            $sub_array[] = strlen($val->product_brand)<=20 ? $val->product_brand : substr($val->product_brand,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->product_brand.'"></i>';
            $sub_array[] = strlen($val->inventory_grouping)<=20 ? $val->inventory_grouping : substr($val->inventory_grouping,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->inventory_grouping.'"></i>';
            // $sub_array[] = $val->opening_stock;
            $sub_array[] = round($mystock,2);
            $sub_array[] = $val->uom_name.'<input type="hidden" id="inventory_addedby_name_'.$val->id.'" value="'.$val->first_name.'">';
            $data1[] = $sub_array;
        }
        // pr($data1); die;
        $output = array(
            "draw"              =>  intval($_POST["draw"]),
            "recordsTotal"      =>  count($data1),
            "recordsFiltered"   =>  $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }
    public function export_stock_ledger(){
        $cat_id=array();

        $user_id        =   $this->session->userdata('auth_user')['users_id'];
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $users_ids  =   $users['parent_id'];
        } else {
            $users_ids   =  $users['users_id'];
        }
        $buyer_users     =  getBuyerUserIdByParentId($users_ids);
        $result          =  $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'page',$cat_id);
        $total_record   =   $this->inventory_management_model->get_inventory_data($users_ids, $buyer_users,'total',$cat_id);
        //pr($result); die;
        $invarrs    =   array();

        if(isset($result) && !empty($result)){
            foreach($result as $resp_val){
                $invarrs[$resp_val->id]=$resp_val->id;
            }
            //===Total Indent Qty ===//
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $ind_qry = $this->db->select('MAX(inventory_id) as inventory_id,SUM(indent_qty) AS total_quantity')->get_where('indent_mgt',array('indent_qty >=' => '0', 'inv_status' => 1, 'is_deleted !=' => 1));

            if($ind_qry->num_rows()){
                foreach($ind_qry->result() as $inds_resp){
                    $totindqty[$inds_resp->inventory_id]=$inds_resp->total_quantity;
                }
            }
            //===Total Indent Qty ===//

        }

        $data1      =   [];
        if(isset($invarrs) && !empty($invarrs)){
            //====TOTAL RFQ===//
            $rfq_qty                        =   array();
            $close_rfq_id_arr               =   array();
            $rfq_ids_against_inventory_id   =   array();
            $rfq_tot_price_id               =   array();
            $rfq_tot_price_inv_id           =   array();
            $this->db->group_by('variant_grp_id');
            $this->db->where_in('inventory_id',$invarrs);
            $rfq_qry = $this->db->select('MAX(id) as id,MAX(rfq_id) as rfq_id,MAX(inventory_id) as inventory_id,MAX(quantity) as quantity,MAX(buyer_rfq_status) as buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($rfq_qry->num_rows()){

                foreach($rfq_qry->result() as $rfq_rows){
                    if($rfq_rows->buyer_rfq_status==8 || $rfq_rows->buyer_rfq_status==10){
                        $close_rfq_id_arr[$rfq_rows->id]    =   $rfq_rows->id;
                        $rfq_ids_against_inventory_id[$rfq_rows->id] = $rfq_rows->inventory_id;
                    }else{
                        $rfq_qty[$rfq_rows->inventory_id] = isset($rfq_qty[$rfq_rows->inventory_id]) ? ($rfq_qty[$rfq_rows->inventory_id] + $rfq_rows->quantity) : ($rfq_rows->quantity);
                    }
                }
            }
            //===For order RFQ===//
            $this->db->where_in('inventory_id',$invarrs);
            $orfq_qry = $this->db->select('id,rfq_id,inventory_id,quantity,buyer_rfq_status')->get_where('tbl_rfq',array('record_type' => '2', 'inv_status' => '1'));
            if($orfq_qry->num_rows()){
                foreach($orfq_qry->result() as $rfq_rows){
                    $rfq_tot_price_id[$rfq_rows->id]        =   $rfq_rows->id;
                    $rfq_tot_price_inv_id[$rfq_rows->id]    =   $rfq_rows->inventory_id;
                }
            }
            //===For Order RFQ===//
            //====TOTAL RFQ===//
            //===Closed RFQ Qty=====//
            $close_price_ids    =   array();
            $closed_order       =   array();
            $final_close_order  =   array();
            $get_inv_ids_price  =   array();

            if(isset($close_rfq_id_arr) && !empty($close_rfq_id_arr)){
                $this->db->where_in('rfq_record_id',$close_rfq_id_arr);
                $close_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($close_qry_rfq_price->num_rows()){
                    foreach($close_qry_rfq_price->result() as $rfq_prc_row){
                        $close_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $get_inv_ids_price[$rfq_prc_row->id] = isset($rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id]) ? $rfq_ids_against_inventory_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }

            if(isset($close_price_ids) && !empty($close_price_ids)){
                $this->db->where_in('price_id',$close_price_ids);
                $qry_rfq_order = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array());
                if($qry_rfq_order->num_rows()){
                    foreach($qry_rfq_order->result() as $rfq_ord){
                        $closed_order[$rfq_ord->price_id] = isset($closed_order[$rfq_ord->price_id]) ? $closed_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($closed_order as $crows_key => $crow_val){
                        $final_close_order[$get_inv_ids_price[$crows_key]] = $crow_val;
                    }
                }
            }
            //===Closed RFQ Qty=====//
            //===Place Order====//
            $order_price_ids            =   array();
            $place_order_inv_ids_price  =   array();
            $place_order                =   array();
            $final_place_order          =   array();
            //pr($rfq_tot_price_id); die;
            if(isset($rfq_tot_price_id) && !empty($rfq_tot_price_id)){
                $this->db->where_in('rfq_record_id',$rfq_tot_price_id);
                $ord_qry_rfq_price = $this->db->select('id,rfq_record_id')->get_where('tbl_rfq_price',array());
                if($ord_qry_rfq_price->num_rows()){
                    foreach($ord_qry_rfq_price->result() as $rfq_prc_row){
                        $order_price_ids[$rfq_prc_row->id] = $rfq_prc_row->id;
                        $place_order_inv_ids_price[$rfq_prc_row->id] = isset($rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id]) ? $rfq_tot_price_inv_id[$rfq_prc_row->rfq_record_id] : '';
                    }
                }
            }
            if(isset($order_price_ids) && !empty($order_price_ids)){
                $this->db->where_in('price_id',$order_price_ids);
                $qry_rfq_placeorder = $this->db->select('price_id,order_quantity')->get_where('tbl_rfq_order',array('order_status' => '1'));
                if($qry_rfq_placeorder->num_rows()){
                    foreach($qry_rfq_placeorder->result() as $rfq_ord){
                        $place_order[$rfq_ord->price_id] = isset($place_order[$rfq_ord->price_id]) ? $place_order[$rfq_ord->price_id]+$rfq_ord->order_quantity : $rfq_ord->order_quantity;
                    }
                    foreach($place_order as $crows_key => $crow_val){
                        $final_place_order[$place_order_inv_ids_price[$crows_key]] = isset($final_place_order[$place_order_inv_ids_price[$crows_key]]) ? ($final_place_order[$place_order_inv_ids_price[$crows_key]] + $crow_val) : $crow_val;
                    }
                }
            }
            //pr($final_place_order); die;
            //===Place Order====//
            //===GRN====//
            $new_grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $new_qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('inv_status' => 1, 'grn_type' => '1', 'is_deleted' => '0'));
            if($new_qry_grn_wp->num_rows()){
                foreach($new_qry_grn_wp->result() as $grn_wp_res){
                    $new_grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            $grn_wpo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wp = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '1', 'is_deleted' => '0'));
            if($qry_grn_wp->num_rows()){
                foreach($qry_grn_wp->result() as $grn_wp_res){
                    $grn_wpo_arr[$grn_wp_res->inventory_id]    =   $grn_wp_res->total_grn_quantity;
                }
            }
            //===GRN WPO====//
            //===GRN WOPO====//
            $grn_wopo_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_wop = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array( 'grn_type' => '2', 'is_deleted' => '0'));
            if($qry_grn_wop->num_rows()){
                foreach($qry_grn_wop->result() as $grn_wop_res){
                    $grn_wopo_arr[$grn_wop_res->inventory_id]    =   $grn_wop_res->total_grn_quantity;
                }
            }
            //===GRN WOPO====//
            //===Stock GRN===//
            $grn_stock_arr =   array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_grn_stock = $this->db->select('SUM(grn_qty) AS total_grn_quantity,MAX(inventory_id) as inventory_id')->get_where('grn_mgt',array('grn_type' => '3', 'is_deleted' => '0'));
            if($qry_grn_stock->num_rows()){
                foreach($qry_grn_stock->result() as $grn_stock){
                    $grn_stock_arr[$grn_stock->inventory_id]    =   $grn_stock->total_grn_quantity;
                }
            }
            //===Stock GRN===//
            //===GRN====//

            //===Issued===//
            $issued_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued = $this->db->select('SUM(qty) AS total_issued_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_mgt',array('is_deleted' => '0'));
            if($qry_issued->num_rows()){
                foreach($qry_issued->result() as $issue_res){
                    $issued_arr[$issue_res->inventory_id]    =   $issue_res->total_issued_quantity;
                }
            }
            //===Issued===//

            //====Issued Return===//
            $issued_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_issued_return = $this->db->select('SUM(qty) AS total_ir_quantity,MAX(inventory_id) as inventory_id')->get_where('issued_return_mgt',array('is_deleted' => '0'));
            if($qry_issued_return->num_rows()){
                foreach($qry_issued_return->result() as $issue_ret_res){
                    $issued_return_arr[$issue_ret_res->inventory_id]    =   $issue_ret_res->total_ir_quantity;
                }
            }
            //====Issued Return===//
            //===Stock Return=====//
            $stock_return_arr = array();
            $this->db->where_in('inventory_id',$invarrs);
            $this->db->group_by('inventory_id');
            $qry_stock_return = $this->db->select('SUM(qty) AS total_stock_return,MAX(inventory_id) as inventory_id')->get_where('tbl_return_stock',array('is_deleted' => '0'));
            if($qry_stock_return->num_rows()){
                foreach($qry_stock_return->result() as $stock_ret_res){
                    $stock_return_arr[$stock_ret_res->inventory_id]    =   $stock_ret_res->total_stock_return;
                }
            }
            //===Stock Return=====//
        }
            // pr($new_grn_wpo_arr);die;
        // pr($issued_return_arr);
         $data = array();
         $i = 0;
         $final_data = array();
        foreach ($result as $key => $val) {
            //===Indent Qty==//
            $total_quantity = isset($totindqty) && isset($totindqty[$val->id]) ? $totindqty[$val->id] : 0;
            //===Indent Qty===//
            //====RFQ QTY ====//
            $total_RFQ = isset($rfq_qty[$val->id]) ? $rfq_qty[$val->id] : 0;
            if(isset($final_close_order[$val->id])){
                $total_RFQ = $total_RFQ+$final_close_order[$val->id];
            }
            //===RFQ QTY======//
            //====Place Order===//
            $totl_order =   isset($final_place_order[$val->id]) ? $final_place_order[$val->id] : 0;
            //====Place Order===//
            ///   new  grn /////
            $new_grn_qty = 0;
            if(isset($new_grn_wpo_arr[$val->id])){
                $new_grn_qty = $new_grn_wpo_arr[$val->id];
            }
            //===GRN====//
            $grn_qty = 0;
            if(isset($grn_wpo_arr[$val->id])){
                $grn_qty = $grn_wpo_arr[$val->id];
            }
            $grn_qty_wop = 0;
            if(isset($grn_wopo_arr[$val->id])){
                $grn_qty_wop = $grn_wopo_arr[$val->id];
            }
            $grn_qty_stok = 0;
            if(isset($grn_stock_arr[$val->id])){
                $grn_qty_stok = $grn_stock_arr[$val->id];
            }

            //====GRN====//
            //===Issued=====//
            $issued_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_qty = $issued_arr[$val->id];
            }
            //===Issued=====//
            //===Isseued Return==//
            $issued_return_qty = 0;
            if(isset($issued_arr[$val->id])){
                $issued_return_qty = $issued_return_arr[$val->id];
            }
            //===Issued Return===//
            //===Stock Return===//
            $stock_return_qty = 0;
            if(isset($stock_return_arr[$val->id])){
                $stock_return_qty = $stock_return_arr[$val->id];
            }
            //===Stock Return====//
            $sub_array = array();
            //$mystock    =  ($val->opening_stock+$total_grn_qty+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $mystock    =   ($val->opening_stock+$grn_qty+$grn_qty_wop+$grn_qty_stok+$issued_return_qty)-($issued_qty+$stock_return_qty);
            $is_del_invs     =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="1">';
            if($val->opening_stock>=1 || $val->is_indent==1){
                $is_del_invs =   '<input type="hidden" id="is_del_inventory_'.$val->id.'" value="0">';
            }
            //$sub_array[] = '<span data-toggle="collapse" style="cursor: pointer; display:none" id="minus_'.$val->id.'"  class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="close_indent_tds('.$val->id.')"><i class="bi bi-dash-lg"></i></span><span data-toggle="collapse" style="cursor: pointer" id="plus_'.$val->id.'" class="pr-2 accordion_parent accordion_parent_'.$val->id.'" tab-index="'.$val->id.'" onclick="open_indent_tds('.$val->id.')"><i class="bi bi-plus-lg"></i></span> <input type="checkbox" class="inventory_chkd" name="inv_checkbox" id="inv_checkbox_'.$val->id.'" value="'.$val->id.'">';
            $sub_array[] = $key+1;
            if(isset($val->indent_min_qty) && $val->indent_min_qty>0&& $mystock<=$val->indent_min_qty){
                $sub_array[] = $val->product_name.'<button type="button" class="btn  position-relative" style="color:white !important; background: #015294 !important; border-color:#015294 !important;">Min Qty<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill " style="background:red!important; color: white !important; padding-left:2px!impotant;">'.$val->indent_min_qty.'</span></button>';
            }
            else{
                $sub_array[] = $val->prod_name;
            }
            //$sub_array[] = $finldivcat['division_name'].'/'.$finldivcat['category_name'];
            //$sub_array[] = $finldivcat['category_name'];
            $sub_array[] = $val->specification;
            $sub_array[] = $val->size.$is_del_invs;
            $sub_array[] = $val->uom_name.'<input type="hidden" id="inventory_addedby_name_'.$val->id.'" value="'.$val->first_name.'">';

            $sub_array[] = $val->inventory_grouping;
            //$sub_array[] = $val->opening_stock;
            $sub_array[] = round($mystock,2);

            //$sub_array[] = '<span id="total_never_order'.$val->id.'" style="display:none;padding:0px">'.$never_order.'</span>';
            $data1[] = $sub_array;
            $final_data[$i]['Branch']               =   $val->factory_name;
            $final_data[$i]['Product']              =   $val->prod_name;//$val->product_name;
            $final_data[$i]['Category']             =   $val->cat_name;//$val->product_name;
            $final_data[$i]['Our Product Name']     =   HtmlDecodeString($val->buyer_product_name);//$val->product_name;
            $final_data[$i]['Specification']        =   HtmlDecodeString($val->specification);
            $final_data[$i]['Size']                 =   HtmlDecodeString($val->size);//$val->size;
            $final_data[$i]['brand']                =   HtmlDecodeString($val->product_brand);
            $final_data[$i]['grouping']             =  $val->inventory_grouping;
            $final_data[$i]['Current Stock']        =   round($mystock,2);
            $final_data[$i]['UOM']                  =   $val->uom_name;
            // $final_data[$i]['RFQ Qty']      =   isset($total_RFQ) ? $total_RFQ : 0;
            // $final_data[$i]['Order Qty']    =   isset($totl_order) ? $totl_order : 0;
            // $final_data[$i]['GRN Qty']      =   isset($grn_qty) ? $grn_qty : 0;
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function product_wise_stock_ledger($inventory_id){
        $query = $this->db
        ->select('tbl_product_master.prod_name, tbl_product_master.cat_id, inventory_mgt.product_id,inventory_mgt.id,inventory_mgt.branch_id,tbl_uom.uom_name,inventory_mgt.specification,inventory_mgt.size')
        ->from('tbl_product_master')
        ->join('inventory_mgt', 'tbl_product_master.prod_id = inventory_mgt.product_id', 'inner')
        ->join('buyer_factory_details', 'buyer_factory_details.id = inventory_mgt.branch_id', 'inner')
        ->join('tbl_uom', 'tbl_uom.id = inventory_mgt.uom', 'inner')
        ->where('inventory_mgt.id', $inventory_id)
        ->get();


        // Check if a row exists
        if ($query->num_rows() > 0) {
            $data['prod_name'] =$query->row()->prod_name;
            $data['product_id'] =$query->row()->product_id;
            $data['branch_id'] =$query->row()->branch_id;
            $data['uom_name'] =$query->row()->uom_name;
            $data['cat_id'] =$query->row()->cat_id;
            $data['inventory_id'] =$query->row()->id;
            $data['specification'] =$query->row()->specification;
            $data['size'] =$query->row()->size;
        }
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Product Wise Stock Ledger";
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $data['locations']        =   $this->inventory_management_model->get_locations($user_id);
        // $data['uom_list']           =   getUOMList();
        // $data['inventory_type']     =   GetInventoryType();
        // $data['issued_types']        =   GetIssuedTypes();
        $buyer_currency             =   $this->inventory_management_model->get_buyer_currency($user_id);
        $data['currency_list']      =   _get_buyer_currency($buyer_currency);
        $data['buyer_currency']     =   $buyer_currency;
        $this->load->view('inventory_management/product_wise_stock_ledger',$data);
    }

    public function get_product_wise_stock_ledger_report_data()
    {
    //    die($_POST['start']);
        $stock_ledger_form_date=$this->input->post('stock_ledger_form_date', true);
        $stock_ledger_to_date=$this->input->post('stock_ledger_to_date', true);
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);


        $result = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, 'page',$_POST['start'],$_POST['length']);
        $total_record = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, 'total');
        // pr($result); die;
        $po_numbers = array();
        $po_by_id = array();
        $price_by_po = array();
        $get_grn_type = array();
        $with_po_price = array();

        $invarrs = array();
        $totindqty = array();
        $no_inven_data = [];
        $sr_no = 1;
        $data1 = array();
        $total_qty=0;
        $total_price=0;
        if($_POST['start']>0){
            $paginationresult = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, 'page',0,$_POST['start']);
            foreach ($paginationresult as $key => $val) {
                if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
                    $total_qty=$total_qty+$val->qty;
                    $total_price=round($total_price,2)+round(($val->qty*round($val->price,2)),2);
                }
                if($val->issued_status=='1'||$val->stock_return_status=='1'){
                    $total_qty=$total_qty-$val->qty;
                    $total_price=round($total_price,2)-round(($val->qty*round($val->price,2)),2);
                }
            }
        }
        if(isset($stock_ledger_form_date) && isset($stock_ledger_to_date) && strlen($stock_ledger_form_date)>0 && ($stock_ledger_to_date)>0 ){
            $last_stock = $this->inventory_management_model->get_last_stock_ledger_data($users_ids, $buyer_users, $stock_ledger_form_date);
            // var_dump($last_stock);
            // die('test');
            foreach ($last_stock as $key => $val) {
                if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
                    $total_qty=$total_qty+$val->qty;
                    $total_price=round($total_price,2)+round(($val->qty*round($val->price,2)),2);
                }
                if($val->issued_status=='1'||$val->stock_return_status=='1'){
                    $total_qty=$total_qty-$val->qty;
                    $total_price=round($total_price,2)-round(($val->qty*round($val->price,2)),2);
                }
            }
                $sub_array = array();
                $sub_array[] = '';
                $sub_array[] = '';
                $sub_array[] = '';
                $sub_array[] = '';
                $sub_array[] = $total_qty;
                $sub_array[] = number_format($total_price, 2, '.', ',');
                $sub_array[] = '';
                $sub_array[] = '';
                $sub_array[] = $total_qty;
                $sub_array[] = number_format($total_price, 2, '.', ',');
                $data1[] = $sub_array;
                $sr_no++;

        }

        foreach ($result as $key => $val) {
            $sub_array = array();
            //listing------
            $sub_array[] = date("d/m/Y", strtotime($val->date_field));
            if($val->opening_stock_status=='1'){
                $sub_array[] = '<p style="color:green">Opening Stock</p>';
            }
            if($val->grn_status=='1'){
                $sub_array[] = '<p style="color:green">GRN</p>';
            }
            if($val->issued_status=='1'){
                $sub_array[] = '<p style="color:red">Issued</p>';
            }
            if($val->issue_return_status=='1'){
                $sub_array[] = '<p style="color:green">Issue Return</p>';
            }
            if($val->stock_return_status=='1'){
                $sub_array[] = '<p style="color:red">Stock Return</p>';
            }
            $sub_array[] = $val->transaction_no;
            $sub_array[] = $val->ref_no;
            if($val->opening_stock_status=='1' ||$val->grn_status=='1'||$val->issue_return_status=='1'){
                $sub_array[] = round($val->qty,2);
                // $sub_array[] =  formatIndianRupees($val->qty*$val->price);
                $qty_prices         =   round($val->qty*round($val->price,2),2);
                if($qty_prices>=1){
                    $formatted_price    =   formatIndianRupees($qty_prices);
                }
                else{
                    $formatted_price = $qty_prices >= '.01' ? $qty_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $sub_array[]=$formatted_price;
                $sub_array[] = '';
                $sub_array[] = '';
            }
            if($val->issued_status=='1' || $val->stock_return_status=='1'){
                $sub_array[] = '';
                $sub_array[] = '';
                $sub_array[] = round($val->qty,2);
                // $sub_array[] = formatIndianRupees($val->qty*$val->price);
                $qty_prices         =   round($val->qty*round($val->price,2),2);
                if($qty_prices>=1){
                    $formatted_price    =   formatIndianRupees($qty_prices);
                }
                else{
                    $formatted_price = $qty_prices >= '.01' ? $qty_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $sub_array[]=$formatted_price;
            }

            if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
                $total_qty=$total_qty+$val->qty;
                $total_price=round($total_price,2)+round(($val->qty*round($val->price,2)),2);
            }
            if($val->issued_status=='1'||$val->stock_return_status=='1'){
                $total_qty=$total_qty-$val->qty;
                $total_price=round($total_price,2)-(round($val->qty*round($val->price,2),2));
            }
            $sub_array[] = round($total_qty,2);
            // $sub_array[] = formatIndianRupees($total_price);
            $qty_prices     =   round($total_price,2);
            if($qty_prices>=1){
                $formatted_price    =   formatIndianRupees($qty_prices);
            }
            else{
                $formatted_price = $qty_prices >= '.01' ? $qty_prices : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            $sub_array[]=$formatted_price;



            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1,
            "start"=>$_POST['start']
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function new_search_product_for_supplier() {
        // Sanitize and prepare the input
        $search_data = preg_replace('/\s+/', ' ', $this->input->post('search_data'));
        $search_type = $this->input->post('search_type');
        //$store_id = $this->input->post('store_id');
        $product_search = $this->security->xss_clean(trim($search_data));

        // if (empty($store_id)) {
        //     $user_login = $this->session->userdata('system_admin');
        //     $store_id = $user_login['store_id'];
        // }

        // Split the search data into individual keywords
        $search_key_arr = explode(' ', $product_search);

        // Prepare the query for product name search

        $this->db->select("MAX(prod_id) as prod_id, MAX(prod_name) as prod_name, MAX(div_id) as div_id, MAX(cat_id) as cat_id, MAX(div_name) as div_name, MAX(cat_name) as cat_name, MAX(alias) as master_alias");
        $p_name = $tag = '';
        foreach ($search_key_arr as $key => $val) {
            if (!empty($p_name)) {
                $p_name .= " AND ";
                $tag .= " AND ";
            }
            $p_name .= " prod_name like '%$val%' ";
            $tag .= " alias like '%$val%' ";
        }
        $where = " (( " . $p_name . " ) " . " OR " . " ( " . $tag . " )) ";
        $this->db->where($where);
        //$this->db->where('prod_id NOT IN (SELECT prod_id FROM tbl_vendor_product WHERE vend_id = ' . $this->db->escape($store_id) . ')');
        $this->db->group_by("prod_id,prod_name");
        $query = $this->db->get('view_live_master_product_with_alias');
        $total = $query->num_rows();
        $qry = $this->db->last_query();
        // Prepare the response
        if ($total > 0) {
            $all_records = $query->result();
            foreach ($all_records as $key => $value) {
                if (empty($value->master_alias)) {
                    $all_records[$key]->master_alias = '';
                }
            }
            $data['data'] = $all_records;
            $data['totalRecords'] = $total;
            $data['status'] = 'pass';
            $data['page_no'] = '';
        } else {
            $data['search_result'] = '';
            $data['status'] = 'nodata';
        }
        // $data['qry'] = $qry;
        // Return the response
        echo json_encode($data);
    }
    public function export_product_wise_stock_ledger_report(){
        $stock_ledger_form_date=$this->input->post('stock_ledger_form_date', true);
        $stock_ledger_to_date=$this->input->post('stock_ledger_to_date', true);
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);


        $result = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, '','','');
        // $total_record = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, 'total');


        $i=0;
        $data = array();
        $total_qty=0;
        $total_price=0;

        $final_data = array();
        // if($_POST['start']>0){
        //     $paginationresult = $this->inventory_management_model->get_product_wise_stock_ledger_report_data($users_ids, $buyer_users, 'page',0,$_POST['start']);
        //     foreach ($paginationresult as $key => $val) {
        //         if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
        //             $total_qty=$total_qty+$val->qty;
        //             $total_price=$total_price+($val->qty*$val->price);
        //         }
        //         if($val->issued_status=='1'||$val->stock_return_status=='1'){
        //             $total_qty=$total_qty-$val->qty;
        //             $total_price=$total_price-($val->qty*$val->price);
        //         }
        //     }
        // }
        if(isset($stock_ledger_form_date) && isset($stock_ledger_to_date) && strlen($stock_ledger_form_date)>0 && ($stock_ledger_to_date)>0 ){
            // die('test');
            $last_stock = $this->inventory_management_model->get_last_stock_ledger_data($users_ids, $buyer_users, $stock_ledger_form_date);
            foreach ($last_stock as $key => $val) {
                if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
                    $total_qty=round($total_qty,2)+round($val->qty,2);
                    $total_price=round($total_price,2)+(round($val->qty*round($val->price,2),2));
                }
                if($val->issued_status=='1'||$val->stock_return_status=='1'){
                    $total_qty=round($total_qty,2)+round($val->qty,2);
                    $total_price=round($total_price)-(round($val->qty*round($val->price,2),2));
                }
            }
            if($total_price>=1){
                $formatted_price    =   formatIndianRupees($total_price);
            }
            else{
                $formatted_price = $total_price >= '.01' ? $total_price : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            $total_qty=$total_qty >= '.01' ? $total_qty : '0';
            $sub_array              =   array();
            $sub_array[]            =   '';
            $sub_array[]            =   '';
            $sub_array[]            =   '';
            $sub_array[]            =   '';
            $sub_array[]            =   $total_qty;

            $sub_array[]            =   $formatted_price;
            $sub_array[]            =   '';
            $sub_array[]            =   '';
            $sub_array[]            =   $total_qty;
            $sub_array[]            =   $formatted_price;
            $data1[]                =   $sub_array;
            $final_data[$i]['Date'] =   '';
            $final_data[$i]['Particulars / Description']    =   '';
            $final_data[$i]['No.']                          =   '';
            $final_data[$i]['Reference Number']             =   '';
            $final_data[$i]['Inwards Quantity']             =   $total_qty;
            $final_data[$i]['Inwards Total Amount']         =   $formatted_price;
            $final_data[$i]['Outwards Quantity']            =   '';
            $final_data[$i]['Outwards Total Amount']        =   '';
            $final_data[$i]['Closing Quantity']             =   $total_qty;
            $final_data[$i]['Closing Total Amount']         =   $formatted_price;
            $i++;

        }
        foreach ($result as $key => $val) {
            $final_data[$i]['Date']               =  date("d/m/Y", strtotime($val->date_field));
            if($val->opening_stock_status=='1'){
                $final_data[$i]['Particulars / Description'] = 'Opening Stock';
            }
            if($val->grn_status=='1'){
                $final_data[$i]['Particulars / Description']  = 'GRN';
            }
            if($val->issued_status=='1'){
                $final_data[$i]['Particulars / Description'] = 'Issued';
            }
            if($val->issue_return_status=='1'){
                $final_data[$i]['Particulars / Description'] = 'Issue Return';
            }
            if($val->stock_return_status=='1'){
                $final_data[$i]['Particulars / Description'] = 'Stock Return';
            }
            $final_data[$i]['No.']              =   $val->transaction_no;
            $final_data[$i]['Reference Number']              =   $val->ref_no;
            if($val->opening_stock_status=='1' ||$val->grn_status=='1'||$val->issue_return_status=='1'){
                $final_data[$i]['Inwards Quantity']  = $val->qty;
                $qty_prices                                  =   $val->qty*round($val->price,2);
                if($qty_prices>=1){
                    $formatted_price    =   formatIndianRupees($qty_prices);
                }
                else{
                    $formatted_price = $qty_prices >= '.01' ? $qty_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['Inwards Total Amount']     =   $formatted_price;
                $final_data[$i]['Outwards Quantity']   = '';
                $final_data[$i]['Outwards Total Amount'] = '';
            }
            if($val->issued_status=='1' || $val->stock_return_status=='1'){
                //echo "lineno14693".$val->qty.'<br>';
                $final_data[$i]['Inwards Quantity']  = '';
                $final_data[$i]['Inwards Total Amount'] = '';
                $final_data[$i]['Outwards Quantity']  = round($val->qty,2);
                $qty_prices     =   $val->qty*round($val->price,2);
                if($qty_prices>=1){
                    $formatted_price    =   formatIndianRupees($qty_prices);
                }
                else{
                    $formatted_price = $qty_prices >= '.01' ? $qty_prices : '0.00';
                }
                if (strpos($formatted_price, '.') === false) {
                    $formatted_price .= '.00';
                }
                $final_data[$i]['Outwards Total Amount']  = $formatted_price;
            }
            if($val->opening_stock_status=='1'||$val->grn_status=='1'||$val->issue_return_status=='1'){
                $total_qty=round($total_qty,2)+round($val->qty,2);
                //echo "lineno14711-".$total_qty.'<br>';
                $total_price=round($total_price,2)+(round($val->qty*round($val->price,2),2));
            }
            if($val->issued_status=='1' || $val->stock_return_status=='1'){
                //echo "lineno14715-".$total_qty.'<br>';
                //echo "lineno14716-".$val->qty.'<br>';
                $total_qty=round($total_qty,2)-round($val->qty,2);
                $total_price=round($total_price,2)-(round($val->qty*round($val->price,2),2));
            }
            if($total_price>=1){
                $formatted_price    =   formatIndianRupees($total_price);
            }
            else{
                $formatted_price = $total_price >= '.01' ? $total_price : '0.00';
            }
            if (strpos($formatted_price, '.') === false) {
                $formatted_price .= '.00';
            }
            $total_qty=$total_qty >= '.01' ? $total_qty : '0';
            $final_data[$i]['Closing Quantity']                =   $total_qty;
            $final_data[$i]['Closing Total Amount']             =  $formatted_price;
            $i++;
        }
        //die('444');

        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function reset_inventory(){
        $response   =   array();
        if($this->input->is_ajax_request()){
            $inventory_ids   =   $this->input->post('inventory_ids');
            if($inventory_ids){
                //===check is grn====//
                $this->db->where_in('inventory_id',$inventory_ids);
                $grn_qry = $this->db->get_where('grn_mgt',array());
                if($grn_qry->num_rows()){
                    $response['status']     =   '0';
                    $response['message']    =   'GRN Already Processed.';
                    echo json_encode($response); die;
                }
                //===check is grn====//
                //===check is issued====//
                $this->db->where_in('inventory_id',$inventory_ids);
                $issued_mgt_qry = $this->db->get_where('issued_mgt',array());
                if($issued_mgt_qry->num_rows()){
                    $response['status']     =   '0';
                    $response['message']    =   'Issued Already Processed.';
                    echo json_encode($response); die;
                }
                //===check is issued====//
                //===check is stock====//
                $this->db->where_in('inventory_id',$inventory_ids);
                $issued_mgt_qry = $this->db->get_where('tbl_return_stock',array());
                if($issued_mgt_qry->num_rows()){
                    $response['status']     =   '0';
                    $response['message']    =   'Stock Return Already Processed.';
                    echo json_encode($response); die;
                }
                //===check is stock====//
                //==Reset indent===//
                $this->db->where_in('inventory_id',$inventory_ids);
                $qry = $this->db->update('indent_mgt',array('is_deleted' => '1'));
                //==Reset Indent====//
                //==Reset rfq===//
                $this->db->where_in('inventory_id',$inventory_ids);
                $qry = $this->db->update('tbl_rfq',array('inventory_id' => NULL));
                //==Reset rfq====//
                if($qry){
                    $response['status']     =   '1';
                    $response['message']    =   'Inventory reset successfully';
                }
                else{
                    $response['status']     =   '0';
                    $response['message']    =   'Inventory not reset, please try again letter';
                }
            }
            else{
                $response['status']     =   '0';
                $response['message']    =   'Inventory not found';
            }
        }
        echo json_encode($response); die;
    }

    public function pending_grn_stock_return_report(){
        $users          =   $this->session->userdata('auth_user');
        if($users['parent_id'] != '') {
            $user_id  =   $users['parent_id'];
        } else {
            $user_id   =  $users['users_id'];
        }
        $data['page_title']         =   "Pending GRN for Stock Return Report";
        //$user_id                    =   $this->session->userdata('auth_user')['users_id'];
        $data['branch_data']        =   $this->inventory_management_model->get_branch_data($user_id);
        $child_branch               =   getBuyerUserBranchIdOnly();
        if(isset($child_branch) && !empty($child_branch)){
            foreach($data['branch_data'] as $brn_key => $brn_row){
                if(!in_array($brn_row->id,$child_branch)){
                    unset($data['branch_data'][$brn_key]);
                }
            }
        }
        $data['uom_list']           =   getUOMList();
        $this->load->view('inventory_management/pending_grn_stock_return_report',$data);
    }

    public function get_pending_grn_stock_return_data()
    {

        $cat_id         =   array();
        if($_POST['categorys'] != ''){
            $cat_id     =   $this->get_categorys_list($_POST['categorys']);
        }
        $user_id    =   $this->session->userdata('auth_user')['users_id'];
        $users      =   $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users    =   getBuyerUserIdByParentId($users_ids);
        $result         =   $this->inventory_management_model->get_pending_grn_stock_return_data($users_ids, $buyer_users, 'page', $cat_id);
        $total_record   =   $this->inventory_management_model->get_pending_grn_stock_return_data($users_ids, $buyer_users, 'total', $cat_id);
        //pr($result); die;
        $inv_ids    =   array();
        $stock_id   =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $inv_ids[$v->inventory_id]  =   $v->inventory_id;
                $stock_id[]                 =   $v->id;
            }
            //===Get GRN Details ===//
            $grn_details = array();
            $this->db->where_in('stock_id',$stock_id);
            $grn_qry = $this->db->select('id,stock_id,grn_no,grn_qty')->get_where('grn_mgt');
            if($grn_qry->num_rows()){
                foreach($grn_qry->result() as $grn_key=>$grn_vals){
                    if(isset($grn_details[$grn_vals->stock_id])){
                        $grn_details[$grn_vals->stock_id]->grn_no   =   $grn_details[$grn_vals->stock_id]->grn_no.','.$grn_vals->grn_no;
                        $grn_details[$grn_vals->stock_id]->grn_qty  =   $grn_details[$grn_vals->stock_id]->grn_qty+$grn_vals->grn_qty;
                    }
                    else{
                        $grn_details[$grn_vals->stock_id]   =   $grn_vals;
                    }
                }
            }
            //pr($grn_details); die;
            //===Get GRN Details====//
        }
        $sr_no = 1;
        $data1 = array();
        //pr($result); die;
        foreach ($result as $key => $val) {
            $grn_datas  =   isset($grn_details[$val->id]) ? $grn_details[$val->id] : array();
            $sub_array = array();
            //listing------
            $sub_array[]        =   isset($grn_datas->grn_no) ? $grn_datas->grn_no : 'N/A';
            $sub_array[]        =   $val->stock_no;
            $sub_array[]        =   $val->prod_name;
            $sub_array[]        =   strlen($val->specification)<=20 ? $val->specification : substr($val->specification,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->specification.'"></i>';
            $sub_array[]        =   strlen($val->size)<=20 ? $val->size : substr($val->size,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->size.'"></i>';
            $sub_array[]        =   strlen($val->stock_vendor_name)<=20 ? $val->stock_vendor_name : substr($val->stock_vendor_name,0,20).'<i class="bi bi-info-circle-fill" title="'.$val->stock_vendor_name.'"></i>';
            $sub_array[]        =   $val->first_name;
            $sub_array[]        =   date("d/m/Y", strtotime($val->last_updated_date));
            $sub_array[]        =   $val->uom_name;
            $stock_return_qty   =   round($val->qty,2);
            $grn_return_qty     =   isset($grn_datas->grn_qty) ? round($grn_datas->grn_qty,2) : 0;
            $pending_grn        =   round($stock_return_qty,2)-round($grn_return_qty,2);
            $sub_array[]        =   round($stock_return_qty,2);
            $sub_array[]        =   round($grn_return_qty,2);
            $sub_array[]        =   round($pending_grn,2);

            $data1[] = $sub_array;
            $sr_no++;
        }
        // pr($data1); die;
        $output = array(
            "draw" => intval($_POST["draw"]),
            "recordsTotal" => count($data1),
            "recordsFiltered" => $total_record,
            "data" => $data1
        );
        // pr($output); die;
        echo json_encode($output);
    }

    public function export_get_pending_grn_stock_return_data()
    {
        $cat_id = array();
        if($_POST['categorys'] != ''){
            $pre_qry = $this->db->select('cat_id')->get_where('tbl_category',array('cat_name' => $_POST['categorys'], 'status' => '1'));
            if($pre_qry->num_rows()){
                foreach($pre_qry->result() as $rowsss){
                    $cat_id[$rowsss->cat_id]=$rowsss->cat_id;
                }
            }
        }
        $user_id = $this->session->userdata('auth_user')['users_id'];
        $users = $this->session->userdata('auth_user');
        if ($users['parent_id'] != '') {
            $users_ids = $users['parent_id'];
        } else {
            $users_ids = $users['users_id'];
        }
        $buyer_users = getBuyerUserIdByParentId($users_ids);
        $result = $this->inventory_management_model->get_pending_grn_stock_return_data($users_ids, $buyer_users, 'page',$cat_id);
        // print_r($result);
        $total_record = $this->inventory_management_model->get_pending_grn_stock_return_data($users_ids, $buyer_users, 'total', $cat_id);

        $inv_ids    =   array();
        $stock_id   =   array();
        if(isset($result) && !empty($result)){
            foreach($result as $kv => $v){
                $inv_ids[$v->inventory_id]  =   $v->inventory_id;
                $stock_id[]                 =   $v->id;
            }
            //===Get GRN Details ===//
            $grn_details = array();
            $this->db->where_in('stock_id',$stock_id);
            $grn_qry = $this->db->select('id,stock_id,grn_no,grn_qty')->get_where('grn_mgt');
            if($grn_qry->num_rows()){
                foreach($grn_qry->result() as $grn_key=>$grn_vals){
                    if(isset($grn_details[$grn_vals->stock_id])){
                        $grn_details[$grn_vals->stock_id]->grn_no   =   $grn_details[$grn_vals->stock_id]->grn_no.','.$grn_vals->grn_no;
                        $grn_details[$grn_vals->stock_id]->grn_qty  =   $grn_details[$grn_vals->stock_id]->grn_qty+$grn_vals->grn_qty;
                    }
                    else{
                        $grn_details[$grn_vals->stock_id]   =   $grn_vals;
                    }
                }
            }
            //pr($grn_details); die;
            //===Get GRN Details====//
        }

        $final_data = array();
        $i = 0;
        foreach ($result as $key => $val) {
            $grn_datas  =   isset($grn_details[$val->id]) ? $grn_details[$val->id] : array();
            //listing------
            $final_data[$i]['GRN Number']               =   isset($grn_datas->grn_no) ? $grn_datas->grn_no : 'N/A';
            $final_data[$i]['Stock Return No']          =   $val->stock_no;
            $final_data[$i]['Product']                  =   $val->prod_name;
            $final_data[$i]['Specification']            =   HtmlDecodeString($val->specification);
            $final_data[$i]['Size']                     =   HtmlDecodeString($val->size);
            $final_data[$i]['Vendor Name']              =   $val->stock_vendor_name;
            $final_data[$i]['Added BY']                 =   $val->first_name;
            $final_data[$i]['Added Date']               =   date("d/m/Y", strtotime($val->last_updated_date));
            $final_data[$i]['UOM']                      =   $val->uom_name;
            $stock_return_qty                           =   round($val->qty,2);
            $grn_return_qty                             =   isset($grn_datas->grn_qty) ? round($grn_datas->grn_qty,2) : 0;
            $pending_grn                                =   round($stock_return_qty,2)-round($grn_return_qty,2);
            $final_data[$i]['Stock Return Quantity']    =   round($stock_return_qty,2);
            $final_data[$i]['GRN Quantity']             =   round($grn_return_qty,2);
            $final_data[$i]['Pending GRN']              =   round($pending_grn,2);
            $i++;
        }
        $data['count'] = count($final_data);
        $data['data'] = $final_data;
        echo json_encode($data);
    }

    public function get_issued_to_details(){
        if($this->input->is_ajax_request()){
            $resp           =   array();
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $user_id    =   $users['parent_id'];
            } else {
                $user_id    =   $users['users_id'];
            }
            $issue_to_data  =   $this->inventory_management_model->get_issue_to_data($user_id);
            $resp           =   array('status' => 1, 'data' => $issue_to_data);
            echo json_encode($resp);
        }
    }

    public function save_issue_to_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $user_id    =   $users['parent_id'];
            } else {
                $user_id    =   $users['users_id'];
            }
            $data =  $this->inventory_management_model->save_issue_to_data($user_id);
            if($data){
                $resp       =   array('status' => 1, 'msg' => 'Issue To Name Saved');
                echo json_encode($resp);
            }
            else{
                $resp       =   array('status' => 0, 'msg' => 'Issue To Name not Saved, Try Again');
                echo json_encode($resp);
            }
        }
        else{
            $resp       =   array('status' => 0, 'msg' => 'Process Not Allow');
            echo json_encode($resp);
        }
    }

    public function delete_issue_to_data(){
        if($this->input->is_ajax_request()){
            $users          =   $this->session->userdata('auth_user');
            if($users['parent_id'] != '') {
                $user_id    =   $users['parent_id'];
            } else {
                $user_id    =   $users['users_id'];
            }
            $data =  $this->inventory_management_model->delete_issue_to_data($user_id);
            if($data){
                $resp       =   array('status' => 1, 'msg' => 'Issue To Name deleted');
                echo json_encode($resp);
            }
            else{
                $resp       =   array('status' => 0, 'msg' => 'Issue To Name not deleted, Try Again');
                echo json_encode($resp);
            }
        }
    }
}
