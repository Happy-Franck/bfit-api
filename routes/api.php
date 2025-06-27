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
        Route::delete('produit/{produit}', [ProduitController::class, 'destroy']);

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

        //CRUD séance
        Route::get('seance', [SeanceController::class, 'indexSeance']);
        Route::get('seance/{seance}', [SeanceController::class, 'show']);
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

//---------------------------------- REGISTER ----------------------------------//
Route::post('/register', 'App\Http\Controllers\AuthController@register');

//---------------------------------- LOGIN ----------------------------------//
Route::post('/login', 'App\Http\Controllers\AuthController@login');

//---------------------------------- LOGGED ACCESS ----------------------------------//
Route::middleware('auth:sanctum')->group(function () {
    // Route pour la déconnexion (logout)
    Route::post('/logout', 'App\Http\Controllers\AuthController@logout');
});

