<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductDealModel;
use Illuminate\Support\Facades\Storage;
use App\Models\ProductCategoryModel;
use App\Models\ProductModel;

class ProductDealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    | General setup
    */
    public $table           = "product_deal";
    public $column_hidden   = [];
    public $file_storage    = "public/img/product_deal";
    public $field_first     = "id";
    public $field_break     = "created_at";
    public $text_add        = "Add New";

    /*
    | Link crud
    */
    public $base    = "admin";
    public $index   = "admin/product_deal/index";
    public $create  = "admin/product_deal/create";
    public $store   = "admin/product_deal/store";
    public $show    = "admin/product_deal/show";
    public $edit    = "admin/product_deal/edit";
    public $update  = "admin/product_deal/update";
    public $destroy = "admin/product_deal/destroy";


    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dropdown()
    {
        // define dropdown
        $dropdown[0] = DropdownSelectionModel::where('status', 1)->get();
        $dropdown_option[0] = "dropdown_selection_name";

        $data['dropdown'] = $dropdown;         
        $data['dropdown_option'] = $dropdown_option;        
    }    

    public function index()
    {
        $table = $this->table;
        $data['column_hidden'] = $this->column_hidden;
        $data['breadcrumb'] = array(
            "home" => array(
                "text" => "Dashboard", 
                "link" => $this->base,
                "is_active" => "inactive"
            ),
            "product_deal" => array(
                "text" => "Product Deal", 
                "link" => "", 
                "is_active" => "active"
            )
        );
        $data['title'] = "Product Deal";
        $data['index'] = $this->index;
        $data['edit'] = $this->edit;
        $data['create'] = $this->create;
        $data['destroy'] = $this->destroy;
        $data['table_field'] = DB::select("DESCRIBE $table");
        $data['field_break'] = $this->field_break;
        $data['field_first'] = $this->field_first;
        $data['text_add'] = $this->text_add;
        $data['table_data'] = ProductDealModel::all();

        return view('backend.single_page.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dropdown[0] = ProductCategoryModel::where('status', 1)->get();
        $dropdown_option[0] = "product_category_name";
        $dropdown[1] = ProductModel::where('status', 1)->get();
        $dropdown_option[1] = "product_name";          
        $data['dropdown'] = $dropdown;         
        $data['dropdown_option'] = $dropdown_option; 

        $table = $this->table;
        $data['column_hidden'] = $this->column_hidden;
        $data['breadcrumb'] = array(
            "home" => array(
                "text" => "Dashboard", 
                "link" => $this->base,
                "is_active" => "inactive"
            ),
            "product_deal" => array(
                "text" => "Product Deal", 
                "link" => $this->index, 
                "is_active" => "inactive"
            ),
            "create_product_deal" => array(
                "text" => "Create Product Deal", 
                "link" => "#", 
                "is_active" => "active"
            )
        );
        $data['title'] = "Create Product Deal";
        $data['store'] = $this->store;
        $data['index'] = $this->index;
        $data['table_field'] = DB::select("DESCRIBE $table");
        $data['field_first'] = $this->field_first;
        $data['field_break'] = $this->field_break;        

        return view('backend.single_page.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $table = $this->table;
        $index = $this->index;

        $column_hidden = [];
        $table_field = DB::select("DESCRIBE $table");
        $field_first = $this->field_first;        
        $field_break = $this->field_break;        
        $storage = $this->file_storage;

        foreach ($table_field as $key => $value) {
            $field_table = $value->Field;
            $field_type = $value->Type;

            if (in_array($key, $column_hidden)) {
                continue;
            }
            if ($field_table == $field_first){
                continue;
            }
            if ($field_table == $field_break){
                break;
            }                                            
            $arr_field[] = $field_table;
            $arr_field_type[] = $field_type;
            $count = count($arr_field); 
        }

        $insert = new ProductDealModel();
        for ($i=0; $i < $count; $i++) { 
            $text_type = $arr_field_type[$i];
            $text_check = substr($text_type, 0, 3);
            if ($text_check == "cha") {
                if (!empty($request->file($arr_field[$i]))) {
                    $file_temp_name = $request->file($arr_field[$i]);
                    $file_name = uniqid() . '.'. $file_temp_name->getClientOriginalExtension();
                    $path = Storage::putFileAs($storage, $request->file($arr_field[$i]), $file_name);
                    $field_db = $arr_field[$i]; 
                    $insert->$field_db = $file_name;
                }                
            }else{
                $field_db = $arr_field[$i];            
                $insert->$field_db = $request->$field_db;            
            }
        }        
        $insert->save();

        $result = preg_replace("/[^a-zA-Z]/", " ", $table); 
        return redirect(url($index))->with("message", "Success created $result !");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dropdown[0] = ProductCategoryModel::where('status', 1)->get();
        $dropdown_option[0] = "product_category_name";
        $dropdown[1] = ProductModel::where('status', 1)->get();
        $dropdown_option[1] = "product_name";          
        $data['dropdown'] = $dropdown;         
        $data['dropdown_option'] = $dropdown_option; 
                
        $table = $this->table;
        $data['column_hidden'] = $this->column_hidden;
        $data['breadcrumb'] = array(
            "home" => array(
                "text" => "Dashboard", 
                "link" => $this->base,
                "is_active" => "inactive"
            ),
            "product_deal" => array(
                "text" => "Product Deal", 
                "link" => $this->index, 
                "is_active" => "inactive"
            ),
            "edit_product_deal" => array(
                "text" => "Edit Product Deal", 
                "link" => "", 
                "is_active" => "active"
            )            
        );
        $data['title'] = "Edit Product Deal";
        $data['update'] = $this->update;
        $data['index'] = $this->index;
        $data['id'] = $id;
        $data['table_field'] = DB::select("DESCRIBE $table");
        $data['field_first'] = $this->field_first;
        $data['field_break'] = $this->field_break;
        $data['table_content'] = ProductDealModel::find($id);

        return view('backend.single_page.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $table = $this->table;
        $index = $this->index;

        $column_hidden = [];
        $table_field = DB::select("DESCRIBE $table");
        $field_break = $this->field_break;
        $field_first = $this->field_first;
        $storage = $this->file_storage;

        foreach ($table_field as $key => $value) {
            $field_table = $value->Field;
            $field_type = $value->Type;

            if (in_array($key, $column_hidden)) {
                continue;
            }
            if ($field_table == $field_first){
                continue;
            }
            if ($field_table == $field_break){
                break;
            }                                            
            $arr_field[] = $field_table;
            $arr_field_type[] = $field_type;
            $count = count($arr_field); 
        }

        $update = ProductDealModel::find($id);
        for ($i=0; $i < $count; $i++) { 
            $text_type = $arr_field_type[$i];
            $text_check = substr($text_type, 0, 3);
            if ($text_check == "cha") {
                if (!empty($request->file( $arr_field[$i]))) {
                    $file_temp_name = $request->file($arr_field[$i]);
                    $file_name = uniqid() . '.'. $file_temp_name->getClientOriginalExtension();
                    $path = Storage::putFileAs($storage, $request->file($arr_field[$i]), $file_name);
                    $field_db = $arr_field[$i]; 
                    $update->$field_db = $file_name;
                }                
            }else{
                $field_db = $arr_field[$i];            
                $update->$field_db = $request->$field_db;            
            }             
        }        
        $update->update();

        $result = preg_replace("/[^a-zA-Z]/", " ", $table); 
        return redirect(url($index))->with("message", "Success updated $result !");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = $this->table;
        $index = $this->index;

        $findtodelete = ProductDealModel::find($id);
        $findtodelete->delete();

        $result = preg_replace("/[^a-zA-Z]/", " ", $table); 
        return redirect(url($this->index))->with("info", "Success deleted $result !");        
    }
}