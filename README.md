$composer create-project laravel/laravel BaliFit
->Installing laravel/laravel (v10.2.2)

$cd BaliFit

$git init

$git add .

$git commit -m "Za Warudo, Tomare! Toki wo!"

$git branch -M main

$git remote add origin https://github.com/Happy-Franck/BaliFit.git

$git push -u origin main

$php artisan migrate

$php artisan storage:link

//SANCTUM
$composer require laravel/sanctum
$php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
->/config/sanctum.php
$php artisan migrate

//SPATIE
$composer require spatie/laravel-permission
$php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
->/config/permission.php
$php artisan migrate

//Creates roles with spatie
$php artisan make:seeder RolePermissionSeeder
$php artisan migrate:fresh --seed

//Configurer le model role
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;
    ...
}

sjouter les middlewares roles et permissions afin que les routes en fonction des roles de l'user soient accessible
protected $middlewareGroups = [];
protected $middlewareAliases = [];

//AUTH
$php artisan make:controller AuthController
configurer les routes login, register et logout
$php artisan route:list --path=api

                                    [AUTH] [POST]
                                    http://localhost:8000/api/login
                                    email:
                                    password:

                                    [AUTH] [POST]
                                    http://localhost:8000/api/register
                                    name:
                                    email:
                                    password:

                                    [AUTH] [POST] [TOKEN]
                                    http://localhost:8000/api/logout

//PRODUIT 
User 1-->N Produit
$php artisan make:model Produit -crm
$php artisan make:seeder ProduitSeeder
$php artisan migrate:fresh --seed

                                    [PRODUIT] [ADMIN] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/admin/produit

                                    [PRODUIT] [ADMIN] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/admin/produit/{produit}

                                    [PRODUIT] [ADMIN] [POST] [TOKEN] [STORE]
                                    http://localhost:8000/api/admin/produit/{produit}
                                    name:
                                    description:
                                    poid:
                                    price:
                                    image:

[PRODUIT] [ADMIN] [PUT] [TOKEN] [UPDATE]
http://localhost:8000/api/admin/produit/{produit}
name:
description:
poid:
price:
image:

                                    [PRODUIT] [ADMIN] [DELETE] [TOKEN] [DESTROY]
                                    http://localhost:8000/api/admin/produit/{produit}


//ADVICE
User 1-->N Advice
Produit 1-->N Advice
Produit 1-->1 Advice/User
$php artisan make:model Advice -crm
$php artisan migrate:fresh --seed

                                [CHALLENGER] [GET] [TOKEN] [INDEX]
                                http://localhost:8000/api/challenger/produit/

                                [CHALLENGER] [GET] [TOKEN] [SHOW]
                                http://localhost:8000/api/challenger/produit/{produit}

                                [CHALLENGER] [POST] [TOKEN] [STORE]
                                http://localhost:8000/api/challenger/produit/{produit}/commenter
                                comment:
                                note:

[CHALLENGER] [PUT] [TOKEN] [UPDATE]
http://localhost:8000/api/challenger/produit/{produit}/commenter/{advice}
comment:
note:

                                [CHALLENGER] [DELETE] [TOKEN] [DESTROY]
                                http://localhost:8000/api/challenger/produit/{produit}/commenter/{advice}

//ROLE->PERMISSIONS
user 1-->1 Role
role N-->N permission
$php artisan make:controller RoleController

                                    [ADMIN] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/admin/role

[ADMIN] [STORE] [TOKEN] [STORE]
http://localhost:8000/api/admin/role
name:

[ADMIN] [PUT] [TOKEN] [UPDATE]
http://localhost:8000/api/admin/role/{role}
name:

[ADMIN] [DELETE] [TOKEN] [DESTROY]
http://localhost:8000/api/admin/role/{role}

[ADMIN] [POST] [TOKEN] [GIVEPERMISSION]
http://localhost:8000/api/admin/role/{role}/permission-give
permission: (name)

[ADMIN] [DELETE] [TOKEN] [REVOKEPERMISSION]
http://localhost:8000/api/admin/role/{role}/permission-revoke/{permission}


//PERMISSION->ROLES
user 1-->N permission
permission N-->N role
$php artisan make:controller PermissionController

                                    [ADMIN] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/admin/permission

[ADMIN] [STORE] [TOKEN] [STORE]
http://localhost:8000/api/admin/permission
name:

[ADMIN] [PUT] [TOKEN] [UPDATE]
http://localhost:8000/api/admin/permission/{permission}
name:

[ADMIN] [DELETE] [TOKEN] [DESTROY]
http://localhost:8000/api/admin/permission/{permission}

[ADMIN] [POST] [TOKEN] [ASSIGNROLE]
http://localhost:8000/api/admin/permission/{permission}/role-assign
role: (name)

[ADMIN] [DELETE] [TOKEN] [REMOVEROLE]
http://localhost:8000/api/admin/permission/{permission}/role-remove/{role}


//USER (management)
$php artisan make:migration create_coaching_table
$php artisan migrate:fresh --seed
$php artisan make:controller UserController

                                    [ADMIN] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/admin/user

                                    [ADMIN] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/admin/user/{user}

[ADMIN] [DELETE] [TOKEN] [DESTROY]
http://localhost:8000/api/admin/user/{user}

                                    [ADMIN] [POST] [TOKEN] [ASSIGNROLE]
                                    http://localhost:8000/api/admin/user/{user}/assign-role
                                    role: (name)

                                    [ADMIN] [DELETE] [TOKEN] [REMOVEROLE]
                                    http://localhost:8000/api/admin/user/{user}/remove-role/{role}

[ADMIN] [POST] [TOKEN] [GIVEPERMISSION]
http://localhost:8000/api/admin/user/{user}/give-permission
permission: (name)

[ADMIN] [DELETE] [TOKEN] [REVOKEPERMISSION]
http://localhost:8000/api/admin/user/{user}/revoke-permission/{permission}

                                    [ADMIN] [DELETE] [TOKEN] [REMOVECHALLENGER]
                                    http://localhost:8000/api/admin/user/{coach}/remove-challenger/{challenger}

                                    [ADMIN] [POST] [TOKEN] [UPDATECHALLENGERS]
                                    http://localhost:8000/api/admin/user/{user}/challengers-update
                                    new_challengers: [ids]

                                    [ADMIN] [DELETE] [TOKEN] [REMOVECOACH]
                                    http://localhost:8000/api/admin/user/{challenger}/remove-coach/{coach}

                                    [ADMIN] [POST] [TOKEN] [UPDATECOACHS]
                                    http://localhost:8000/api/admin/user/{user}/coachs-update
                                    new_coachs: [ids]

                                    [ADMIN] [GET] [TOKEN] [MYCHALLENGERS]
                                    http://localhost:8000/api/admin/user/{user}/coach-challengers

                                    [ADMIN] [GET] [TOKEN] [MYCOACHS]
                                    http://localhost:8000/api/admin/user/{user}/challenger-coachs

//CATEGORY
$php artisan make:model Category -crm
$php artisan make:seeder CategorySeeder
$php artisan migrate:fresh --seed

                                    [ADMIN] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/admin/category

                                    [COACH] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/coach/category

                                    [CHALLENGER] [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/challenger/category

                                    [ADMIN] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/admin/category/{category}

                                    [ADMIN] [POST] [TOKEN] [STORE]
                                    http://localhost:8000/api/admin/category
                                    name:
                                    image:

                                    [ADMIN] [PUT] [TOKEN] [UPDATE]
                                    http://localhost:8000/api/admin/category/{category}
                                    name:
                                    image:

                                    [ADMIN] [DELETE] [TOKEN] [DESTROY]
                                    http://localhost:8000/api/admin/category/{category}

//TRAINING
$php artisan make:model Training -crm
$php artisan make:migration create_category_training_table
$php artisan migrate:fresh --seed

                                    [COACH [GET] [TOKEN] [INDEX]
                                    http://localhost:8000/api/coach/training

                                    [COACH [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/coach/training/{training}

                                    [COACH [POST] [TOKEN] [STORE]
                                    http://localhost:8000/api/coach/training
                                    name:
                                    description:
                                    image:
                                    video:
                                    categories: [ids]

                                    [COACH [PUT] [TOKEN] [UPDATE]
                                    http://localhost:8000/api/coach/training/{training}
                                    name:
                                    description:
                                    image:
                                    video:
                                    categories: [ids]

                                    [COACH [DELETE] [TOKEN] [DESTROY]
                                    http://localhost:8000/api/coach/training/{training}

//SEANCE
$php artisan make:model Seance -crm
$php artisan make:migration create_seance_training_table
$php artisan migrate:fresh --seed

                                    [ADMIN] [GET] [TOKEN] [INDEXSEANCE]
                                    http://localhost:8000/api/admin/seance

                                    [COACH] [GET] [TOKEN] [INDEXCOACH]
                                    http://localhost:8000/api/coach/seance

                                    [CHALLENGER] [GET] [TOKEN] [INDEXCHALLENGER]
                                    http://localhost:8000/api/challenger/seance

                                    [ADMIN] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/admin/seance/{seance}

                                    [COACH] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/coach/seance/{seance}

                                    [CHALLENGER] [GET] [TOKEN] [SHOW]
                                    http://localhost:8000/api/challenger/seance/{seance}

                                    [ADMIN] [POST] [TOKEN] [ASSIGNSEANCECOACHCHALLENGER]
                                    http://localhost:8000/api/admin/users/{coach}/assign-challenger
                                    challenger_id: id

                                    [ADMIN] [POST] [TOKEN] [ASSIGNSEANCECHALLENGERCOACH]
                                    http://localhost:8000/api/admin/users/{challenger}/assign-coach
                                    coach_id: id

                                    [CHALLENGER] [POST] [TOKEN] [STORECHALLENGER]
                                    http://localhost:8000/api/challenger/seance
                                    img_debut:
                                    img_fin:
                                    traininglist[]: [
                                                        series[],
                                                        repetition[],
                                                        duree[]
                                                    ]

                                    [COACH] [PUT] [TOKEN] [UPDATEIMGDEBUT]
                                    http://localhost:8000/api/coach/seance/{seance}/update-debut
                                    img_debut:

                                    [COACH] [PUT] [TOKEN] [UPDATEIMGFIN]
                                    http://localhost:8000/api/coach/seance/{seance}/update-fin
                                    img_fin:

                                    [COACH] [PUT] [TOKEN] [UPDATESUPPRIMGDEBUT]
                                    http://localhost:8000/api/coach/seance/{seance}/suppr-debut
                                    suppr: null

                                    [COACH] [PUT] [TOKEN] [UPDATESUPPRIMGFIN]
                                    http://localhost:8000/api/coach/seance/{seance}/suppr-fin
                                    suppr: null

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATESUPPRCHALLENGERDEBUT]
                                    http://localhost:8000/api/coach/seance/{seance}/suppr-debut
                                    suppr: null

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATESUPPRCHALLENGERFIN]
                                    http://localhost:8000/api/coach/seance/{seance}/suppr-fin
                                    suppr: null

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATEIMGDEBUT]
                                    http://localhost:8000/api/coach/seance/{seance}/update-debut
                                    img_debut:

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATEIMGFIN]
                                    http://localhost:8000/api/coach/seance/{seance}/update-fin
                                    img_fin:

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATEIMGDEBUT]
                                    http://localhost:8000/api/challenger/seance/{seance}/update-debut
                                    img_debut:

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATEIMGFIN]
                                    http://localhost:8000/api/challenger/seance/{seance}/update-fin
                                    img_fin:

                                    [COACH] [PUT] [TOKEN] [UPDATEVALIDER]
                                    http://localhost:8000/api/coach/seance/{seance}/valider

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATEDE$,eance/{seance}/confirmer

                                    [COACH] [POST] [TOKEN] [ADDTRAININGS]
                                    http://localhost:8000/api/coach/seance/{seance}/add-training
                                    traininglist[]: [
                                                        series[],
                                                        repetition[],
                                                        duree[]
                                                    ]

                                    [CHALLENGER] [POST] [TOKEN] [ADDTRAININGS]
                                    http://localhost:8000/api/challenger/seance/{seance}/add-trainings
                                    traininglist[]: [
                                                        series[],
                                                        repetition[],
                                                        duree[]
                                                    ]

                                    [COACH] [PUT] [TOKEN] [UPDATETRAININGS]
                                    http://localhost:8000/api/coach/seance/{seance}/update-trainings
                                    traininglist[]: [
                                                        series[],
                                                        repetition[],
                                                        duree[]
                                                    ]

                                    [CHALLENGER] [PUT] [TOKEN] [UPDATETRAININGS]
                                    http://localhost:8000/api/challenger/seance/{seance}
                                    traininglist[]: [
                                                        series[],
                                                        repetition[],
                                                        duree[]
                                                    ]

                                    [COACH] [DELETE] [TOKEN] [DELETETRAINING]
                                    http://localhost:8000/api/coach/seance/{seance}/delete/{training}/{id}

                                    [CHALLENGER] [DELETE] [TOKEN] [DELETETRAINING]
                                    http://localhost:8000/api/challenger/seance/{seance}/delete/{training}/{id}

                                    [ADMIN] [DELETE] [TOKEN] [DESTROYSEANCE]
                                    http://localhost:8000/api/admin/seance/{seance}

                                    [CHALLENGER] [DELETE] [TOKEN] [DESTROYCHALLENGERSEANCE]
                                    http://localhost:8000/api/challenger/seance/{seance}

{
  "name": "plance",
  "description": "ok ary eh",
  "categories": [1,2]
}
