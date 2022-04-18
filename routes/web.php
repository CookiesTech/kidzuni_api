<?php


$router->get('/', function () use ($router) {
    return $router->app->version();
});
$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('register', 'AuthController@register');
    $router->post('login', 'AuthController@login');
    $router->post('forgot_password', 'AuthController@forgot_password');
    $router->post('update_password', 'AuthController@update_password');
    $router->post('admin_login', 'AuthController@admin_login');
    $router->get('profile', 'TeacherController@profile');
    $router->post('teacher_add', 'TeacherController@add');
    $router->get('getAll', 'TeacherController@getAll');
    $router->get('getActive', 'TeacherController@getActive');
    $router->get('get_teacherProfile/{id}', 'TeacherController@get_teacherProfile');
    $router->post('teacher_update', 'TeacherController@update');
    $router->post('delete_teacher/{id}', 'TeacherController@delete_teacher');
    $router->post('/getStandardandSubjects','StandardController@getStandardandSubjects');
    $router->post('/add_subject', 'SubjectController@add');
    $router->post('/getAllSubjects', 'SubjectController@getAll');
    $router->get('/delete_subject/{id}', 'SubjectController@delete_subject');
    $router->get('getSubjectByID/{id}', 'SubjectController@edit');
    $router->post('subject_update', 'SubjectController@update');

    $router->post('getAllStandard', 'StandardController@getAll');
    $router->post('add_standard', 'StandardController@add');
    $router->post('getStandardsByCountryId','StandardController@getStandardsByCountryId');
    $router->get('delete_subject/{id}', 'StandardController@delete');

    $router->get('getAllmainCategory', 'MainCategoryController@getAll');
    $router->post('add_mainCategory', 'MainCategoryController@add');
    $router->get('delete_mainCategory/{id}', 'MainCategoryController@delete');
    $router->post('getMainCategoryByStandardId','MainCategoryController@getMainCategoryByStandardId');

    $router->get('getAllsubCategory', 'SubCategoryController@getAll');
    $router->post('add_subCategory', 'SubCategoryController@add');
    $router->get('delete_subCategory/{id}', 'SubCategoryController@delete');
   
    $router->get('getAllQuestions', 'QuestionController@getAll');
    $router->post('upload_question', 'QuestionController@upload_question');

     $router->post('getTopics','TopicsController@getTopics');

     $router->get('getAllCountry','CountryController@getAll');
     $router->post('add_country','CountryController@add');
    $router->get('delete_country/{id}', 'CountryController@delete_country');

    $router->get('getAllpackages','PackageController@getAll');
    $router->post('add_package','PackageController@add');
    $router->post('getPackage','PackageController@getPackage');

    $router->post('add_kids','ParentController@add_kids');
    $router->get('getAllParents','ParentController@getAllParents');


    $router->post('getQuestionsByID','QuestionController@getQuestionsByID');
    $router->post('getrecommendations','QuestionController@getrecommendations');

    $router->post('insert_quiztestdata','QuestionController@insert_quiztestdata');
});
