<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\ProductStockModel;
use App\Models\ProductModel;
use App\Models\ProductCategoryModel;
use Illuminate\Support\Facades\Storage;

class ProductStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*
    | General setup
    */
    public $table           = "product_stock";
    public $column_hidden   = [];
    public $file_storage    = "public/img/product_stock";
    public $field_first     = "id";
    public $field_break     = "created_at";
    public $text_add        = "Add New";

    /*
    | Link crud
    */
    public $base    = "admin";
    public $index   = "admin/product_stock/index";
    public $create  = "admin/product_stock/create";
    public $store   = "admin/product_stock/store";
    public $show    = "admin/product_stock/show";
    public $edit    = "admin/product_stock/edit";
    public $update  = "admin/product_stock/update";
    public $destroy = "admin/product_stock/destroy";


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
            "product_stock" => array(
                "text" => "Product Stock", 
                "link" => "", 
                "is_active" => "active"
            )
        );
        $data['title'] = "Product Stock";
        $data['index'] = $this->index;
        $data['edit'] = $this->edit;
        $data['create'] = $this->create;
        $data['destroy'] = $this->destroy;
        $data['table_field'] = DB::select("DESCRIBE $table");
        $data['field_break'] = $this->field_break;
        $data['field_first'] = $this->field_first;
        $data['text_add'] = $this->text_add;
        $data['table_data'] = ProductStockModel::all();

        return view('backend.single_page.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dropdown[0] = ProductModel::where('status', 1)->get();
        $dropdown_option[0] = "product_name";
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
            "product_stock" => array(
                "text" => "Product Stock", 
                "link" => $this->index, 
                "is_active" => "inactive"
            ),
            "create_product_stock" => array(
                "text" => "Create Product Stock", 
                "link" => "#", 
                "is_active" => "active"
            )
        );
        $data['title'] = "Create Product Stock";
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

        $insert = new ProductStockModel();
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
        $dropdown[0] = ProductModel::where('status', 1)->get();
        $dropdown_option[0] = "product_name";
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
            "product_stock" => array(
                "text" => "Product Stock", 
                "link" => $this->index, 
                "is_active" => "inactive"
            ),
            "edit_product_stock" => array(
                "text" => "Edit Product Stock", 
                "link" => "", 
                "is_active" => "active"
            )            
        );
        $data['title'] = "Edit Product Stock";
        $data['update'] = $this->update;
        $data['index'] = $this->index;
        $data['id'] = $id;
        $data['table_field'] = DB::select("DESCRIBE $table");
        $data['field_first'] = $this->field_first;
        $data['field_break'] = $this->field_break;
        $data['table_content'] = ProductStockModel::find($id);

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

        $update = ProductStockModel::find($id);
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

        $findtodelete = ProductStockModel::find($id);
        $findtodelete->delete();

        $result = preg_replace("/[^a-zA-Z]/", " ", $table); 
        return redirect(url($this->index))->with("info", "Success deleted $result !");        
    }
}