<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdviceController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductAttributeController;
use App\Http\Controllers\ProductTypeAttributeController;
use App\Http\Controllers\ProductVariantController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//---------------------------------- DEFAULT ----------------------------------//
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'user' => $request->user(),
    ], 200);
});



//---------------------------------- ADMINISTRATEUR ACCESS ----------------------------------//
Route::middleware(['auth:sanctum','role:administrateur'])->prefix("/admin")->group(
    function(){
        // CRUD produit
        Route::get('produit', [ProduitController::class, 'index']);
        Route::post('produit', [ProduitController::class, 'store']);
        Route::get('produit/{produit}', [ProduitController::class, 'show']);
        Route::put('produit/{produit}', [ProduitController::class, 'update']);
        Route::post('produit/{produit}', [ProduitController::class, 'update']); // Route POST pour FormData
        Route::delete('produit/{produit}', [ProduitController::class, 'destroy']);
        Route::patch('produit/{produit}/toggle-status', [ProduitController::class, 'toggleStatus']);

        // CRUD variantes de produits
        Route::get('produit/{produit}/variants', [ProductVariantController::class, 'index']);
        Route::post('produit/{produit}/variants', [ProductVariantController::class, 'store']);
        Route::get('produit/{produit}/variants/{variant}', [ProductVariantController::class, 'show']);
        Route::put('produit/{produit}/variants/{variant}', [ProductVariantController::class, 'update']);
        Route::delete('produit/{produit}/variants/{variant}', [ProductVariantController::class, 'destroy']);
        Route::patch('produit/{produit}/variants/{variant}/stock', [ProductVariantController::class, 'updateStock']);
        Route::patch('produit/{produit}/variants/{variant}/toggle-status', [ProductVariantController::class, 'toggleStatus']);

        // CRUD type de produit
        Route::get('product-type', [ProductTypeController::class, 'index']);
        Route::post('product-type', [ProductTypeController::class, 'store']);
        Route::get('product-type/{productType}', [ProductTypeController::class, 'show']);
        Route::put('product-type/{productType}', [ProductTypeController::class, 'update']);
        Route::delete('product-type/{productType}', [ProductTypeController::class, 'destroy']);
        Route::patch('product-type/{productType}/toggle-status', [ProductTypeController::class, 'toggleStatus']);
        Route::get('product-type/{productType}/attributes', [ProductTypeController::class, 'getAttributes']);

        // Gestion des commandes (admin)
        Route::get('orders', [OrderController::class, 'adminIndex']);
        Route::get('orders/{order}', [OrderController::class, 'adminShow']);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::patch('orders/{order}/mark-paid', [OrderController::class, 'markAsPaid']);

        // CRUD role
        Route::get('role',[RoleController::class, 'index']);
        Route::post('role',[RoleController::class, 'store']);
        Route::put('role/{role}',[RoleController::class, 'update']);
        Route::delete('role/{role}',[RoleController::class, 'destroy']);
        Route::post('role/{role}/permission-give',[RoleController::class, 'permissiongive']);
        Route::delete('role/{role}/permission-revoke/{permission}',[RoleController::class, 'permissionrevoke']);

        // CRUD permission
        Route::get('permission',[PermissionController::class, 'index']);
        Route::post('permission',[PermissionController::class, 'store']);
        Route::put('permission/{permission}',[PermissionController::class, 'update']);
        Route::delete('permission/{permission}',[PermissionController::class, 'destroy']);
        Route::post('permission/{permission}/role-assign',[PermissionController::class, 'assignrole']);
        Route::delete('permission/{permission}/role-remove/{role}',[PermissionController::class, 'removerole']);

        // USER managment
        Route::get('user',[UserController::class,'index']);
        Route::get('coach',[UserController::class,'coach']);
        Route::get('challenger',[UserController::class,'challenger']);
        Route::get('user/{user}',[UserController::class,'show']);
        Route::delete('user/{user}',[UserController::class,'destroy']);
        Route::post('user/{user}/assign-role',[UserController::class,'assignRole']);
        Route::delete('user/{user}/remove-role/{role}',[UserController::class,'removeRole']);
        Route::post('user/{user}/give-permission',[UserController::class,'givePermission']);
        Route::delete('user/{user}/revoke-permission/{permission}',[UserController::class,'revokePermission']);
        Route::delete('user/{coach}/remove-challenger/{challenger}',[UserController::class,'removeChallenger']);
        Route::post('user/{user}/challengers-update',[UserController::class,'updateChallengers']);
        Route::delete('user/{challenger}/remove-coach/{coach}',[UserController::class,'removeCoach']);
        Route::post('user/{user}/coachs-update',[UserController::class,'updateCoachs']);
        Route::get('user/{user}/coach-challengers',[UserController::class,'myChallengers']);
        Route::get('user/{user}/challenger-coachs',[UserController::class,'myCoachs']);

        //CRUD category
        Route::get('category', [CategoryController::class, 'index']);
        Route::get('category/{category}', [CategoryController::class, 'show']);
        Route::post('category', [CategoryController::class, 'store']);
        Route::put('category/{category}', [CategoryController::class, 'update']);
        Route::delete('category/{category}', [CategoryController::class, 'destroy']);

        //CRUD equipment
        Route::get('equipment', [EquipmentController::class, 'index']);
        Route::get('equipment/{equipment}', [EquipmentController::class, 'show']);
        Route::post('equipment', [EquipmentController::class, 'store']);
        Route::put('equipment/{equipment}', [EquipmentController::class, 'update']);
        Route::delete('equipment/{equipment}', [EquipmentController::class, 'destroy']);
        Route::get('equipment/{equipment}/trainings', [EquipmentController::class, 'getTrainingsByEquipment']);

        //CRUD training (admin access)
        Route::get('training', [TrainingController::class, 'index']);
        Route::get('training/{training}', [TrainingController::class, 'show']);
        Route::post('training', [TrainingController::class, 'store']);
        Route::put('training/{training}', [TrainingController::class, 'update']);
        Route::delete('training/{training}', [TrainingController::class, 'destroy']);

        //CRUD séance
        Route::get('seance', [SeanceController::class, 'indexSeance']);
        Route::get('seance/{seance}', [SeanceController::class, 'show']);
        Route::post('seance', [SeanceController::class, 'storeSeance']);
        Route::put('seance/{seance}', [SeanceController::class, 'updateSeance']);
        Route::post('users/{coach}/assign-challenger', [SeanceController::class, 'assignSeanceCoachChallenger']);
        Route::post('users/{challenger}/assign-coach', [SeanceController::class, 'assignSeanceChallengerCoach']);
        Route::delete('seance/{seance}', [SeanceController::class, 'destroySeance']);
    }
);


//---------------------------------- COACH ACCESS ----------------------------------//
Route::middleware(['auth:sanctum','role:coach'])->name('coach.')->prefix("/coach")->group(
    function(){
        //Get all & my challengers
        Route::get('challenger',[UserController::class,'coachChallengers']);
        Route::get('challenger/{user}',[UserController::class,'showChallenger']);

        //Get all categories
        Route::get('category', [CategoryController::class, 'index']);

        //Get all equipments & trainings by equipment
        Route::get('equipment', [EquipmentController::class, 'index']);
        Route::get('equipment/{equipment}', [EquipmentController::class, 'show']);
        Route::get('equipment/{equipment}/trainings', [EquipmentController::class, 'getTrainingsByEquipment']);

        // CRUD training
        Route::get('training', [TrainingController::class, 'index']);
        Route::get('training/{training}', [TrainingController::class, 'show']);
        Route::post('training', [TrainingController::class, 'store']);
        Route::put('training/{training}', [TrainingController::class, 'update']);
        Route::delete('training/{training}', [TrainingController::class, 'destroy']);

        //Seance managment
        Route::get('seance', [SeanceController::class, 'indexCoach']);
        Route::get('seance/{seance}', [SeanceController::class, 'show']);
        Route::put('seance/{seance}/update-debut', [SeanceController::class, 'updateImgDebut']);
        Route::put('seance/{seance}/suppr-debut', [SeanceController::class, 'updateSupprImgDebut']);
        Route::put('seance/{seance}/update-fin', [SeanceController::class, 'updateImgFin']);
        Route::put('seance/{seance}/suppr-fin', [SeanceController::class, 'updateSupprImgFin']);
        Route::put('seance/{seance}/valider', [SeanceController::class, 'updateValider']);
        Route::post('seance/{seance}/add-trainings', [SeanceController::class, 'addTrainings']);
        Route::put('seance/{seance}/update-trainings', [SeanceController::class, 'updateTrainings']);
        Route::delete('seance/{seance}/delete/{training}/{id}', [SeanceController::class, 'deleteTraining']);
    }
);

//---------------------------------- CHALLENGER ACCESS ----------------------------------//
Route::middleware(['auth:sanctum','role:challenger'])->name('challenger.')->prefix("/challenger")->group(
    function(){
        //Get my coachs
        Route::get('user/{user}/challenger-coachs',[UserController::class,'myCoachs']);

        //Product, advices & comments
        Route::get('/produit', [ProduitController::class, 'index']);
        Route::get('/produit/{produit}', [ProduitController::class, 'show']);
        Route::post('/produit/{produit}/commenter', [AdviceController::class, 'store']);
        Route::delete('/produit/{produit}/commenter/{advice}', [AdviceController::class, 'destroy']);
        Route::put('/produit/{produit}/commenter/{advice}', [AdviceController::class, 'update']);
        
        //Get all categories
        Route::get('category', [CategoryController::class, 'index']);
        
        //Get all equipments & trainings by equipment
        Route::get('equipment', [EquipmentController::class, 'index']);
        Route::get('equipment/{equipment}', [EquipmentController::class, 'show']);
        Route::get('equipment/{equipment}/trainings', [EquipmentController::class, 'getTrainingsByEquipment']);
        
        //get trainings
        Route::get('training', [TrainingController::class, 'indexChallenger']);
        Route::get('training/{training}', [TrainingController::class, 'showChallenger']);

        //Séance management
        Route::get('seance', [SeanceController::class, 'indexChallenger']); //ok
        Route::get('seance/{seance}', [SeanceController::class, 'show']); //ok
        Route::post('seance', [SeanceController::class, 'storeChallenger']); //ok
        Route::put('seance/{seance}/decliner', [SeanceController::class, 'updateDecliner']);
        Route::put('seance/{seance}/confirmer', [SeanceController::class, 'updateConfirmer']);
        Route::post('seance/{seance}/add-trainings', [SeanceController::class, 'addTrainingsChallenger']);
        Route::put('seance/{seance}', [SeanceController::class, 'updateTrainings']);
        Route::delete('seance/{seance}/delete/{training}/{id}', [SeanceController::class, 'deleteTraining']); //ok
        Route::delete('seance/{seance}', [SeanceController::class, 'destroyChallengerSeance']);
        Route::put('seance/{seance}/update-debut', [SeanceController::class, 'updateChallengerDebut']);
        Route::put('seance/{seance}/suppr-debut', [SeanceController::class, 'updateSupprChallengerDebut']);
        Route::put('seance/{seance}/update-fin', [SeanceController::class, 'updateChallengerFin']);
        Route::put('seance/{seance}/suppr-fin', [SeanceController::class, 'updateSupprChallengerFin']);
    }
);

//---------------------------------- E-COMMERCE PUBLIC ROUTES ----------------------------------//
// Routes publiques pour l'e-commerce (consultables sans authentification)
Route::prefix('shop')->group(function () {
    // Types de produits
    Route::get('product-types', [ProductTypeController::class, 'index']);
    Route::get('product-types/{productType}', [ProductTypeController::class, 'show']);
    
    // Produits
    Route::get('products', [ProduitController::class, 'index']);
    Route::get('products/{produit}', [ProduitController::class, 'show']);
});

//---------------------------------- E-COMMERCE AUTHENTICATED ROUTES ----------------------------------//
// Routes protégées pour l'e-commerce (nécessitent une authentification)
Route::middleware('auth:sanctum')->prefix('shop')->group(function () {
    
    // Gestion du panier
    Route::get('cart', [CartController::class, 'index']);
    Route::post('cart', [CartController::class, 'store']);
    Route::put('cart/{cart}', [CartController::class, 'update']);
    Route::delete('cart/{cart}', [CartController::class, 'destroy']);
    Route::delete('cart', [CartController::class, 'clear']);
    Route::get('cart/count', [CartController::class, 'count']);
    
    // Gestion des commandes
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);
    
    // Avis sur les produits (accessible à tous les utilisateurs connectés)
    Route::post('products/{produit}/reviews', [AdviceController::class, 'store']);
    Route::put('products/{produit}/reviews/{advice}', [AdviceController::class, 'update']);
    Route::delete('products/{produit}/reviews/{advice}', [AdviceController::class, 'destroy']);
});

//---------------------------------- REGISTER ----------------------------------//
Route::post('/register', 'App\Http\Controllers\AuthController@register');

//---------------------------------- LOGIN ----------------------------------//
Route::post('/login', 'App\Http\Controllers\AuthController@login');

//---------------------------------- LOGGED ACCESS ----------------------------------//
Route::middleware('auth:sanctum')->group(function () {
    // Route pour la déconnexion (logout)
    Route::post('/logout', 'App\Http\Controllers\AuthController@logout');
    
    // Profile management (accessible à tous les utilisateurs connectés)
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::get('/profile/weight-history', [UserController::class, 'getWeightHistory']);
});

// Product attributes routes
Route::prefix('product-attributes')->group(function () {
    Route::get('/', [ProductAttributeController::class, 'index']);
    Route::get('/all-with-values', [ProductAttributeController::class, 'getAllWithValues']);
    Route::post('/', [ProductAttributeController::class, 'store']);
    Route::get('/{id}', [ProductAttributeController::class, 'show']);
    Route::put('/{id}', [ProductAttributeController::class, 'update']);
    Route::delete('/{id}', [ProductAttributeController::class, 'destroy']);
    
    // Gestion des valeurs d'attributs
    Route::post('/{attributeId}/values', [ProductAttributeController::class, 'storeValue']);
    Route::put('/{attributeId}/values/{valueId}', [ProductAttributeController::class, 'updateValue']);
    Route::delete('/{attributeId}/values/{valueId}', [ProductAttributeController::class, 'destroyValue']);
});

// Product type attributes routes
Route::get('product-types/{productTypeId}/attributes', [ProductTypeAttributeController::class, 'getAttributesByProductType']);
Route::post('product-types/{productTypeId}/attributes', [ProductTypeAttributeController::class, 'attachAttribute']);
Route::delete('product-types/{productTypeId}/attributes/{attributeId}', [ProductTypeAttributeController::class, 'detachAttribute']);
Route::put('product-types/{productTypeId}/attributes/{attributeId}', [ProductTypeAttributeController::class, 'updateAttribute']);

