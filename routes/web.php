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
    $router->post('/getSubjectsByStandard', 'SubjectController@getSubjectsByStandard');
    $router->get('/getAllSubjectsList', 'SubjectController@getAllSubjectsList');
    $router->get('/delete_subject/{id}', 'SubjectController@delete_subject');
    $router->get('getSubjectByID/{id}', 'SubjectController@edit');
    $router->post('subject_update', 'SubjectController@update');
    $router->post('fetchsubjectsByStandardId', 'SubjectController@fetchsubjectsByStandardId');

    $router->get('standard_list', 'StandardController@standard_list');
    $router->post('getAllStandard', 'StandardController@getAll');
    $router->post('add_standard', 'StandardController@add');
    $router->get('/delete_standard/{id}', 'StandardController@delete_standard');
    $router->get('getStandardByID/{id}', 'StandardController@edit');
    $router->post('standard_update', 'StandardController@update');
    $router->post('getStandardsByCountryId','StandardController@getStandardsByCountryId');

    $router->get('getAllmainCategory', 'MainCategoryController@getAll');
    $router->post('add_mainCategory', 'MainCategoryController@add');
    $router->get('delete_mainCategory/{id}', 'MainCategoryController@delete');
    $router->post('getMainCategoryByStandardId','MainCategoryController@getMainCategoryByStandardId');

    $router->get('getAllsubCategory', 'SubCategoryController@getAll');
    $router->post('add_subCategory', 'SubCategoryController@add');
    $router->post('getSubCategoryBymainCategory', 'SubCategoryController@getSubCategoryBymainCategory');
    $router->get('delete_subCategory/{id}', 'SubCategoryController@delete');
   
    $router->get('getAllQuestions', 'QuestionController@getAll');
    $router->post('upload_question', 'QuestionController@upload_question');
    $router->post('insert_question', 'QuestionController@insert_question');
    $router->get('question_details/{id}', 'QuestionController@question_details');

     $router->post('getTopics','TopicsController@getTopics');

     $router->get('getAllCountry','CountryController@getAll');
     $router->post('add_country','CountryController@add');
    $router->get('delete_country/{id}', 'CountryController@delete_country');

    $router->get('getAllpackages','PackageController@getAll');
    $router->post('add_package','PackageController@add');
    $router->post('getPackage','PackageController@getPackage');

    $router->post('add_kids','ParentController@add_kids');
    $router->get('get_kidz_details','ParentController@get_kidz_details');
    $router->get('getAllParents','ParentController@getAllParents');
    $router->get('getAllschools','SchoolController@getAllschools');

    $router->post('getQuestionsByID','QuestionController@getQuestionsByID');

    $router->post('insert_quiztestdata','QuestionController@insert_quiztestdata');
    $router->post('getTestResults','QuestionController@getTestResults');
#AnalyticsPart Student
    $router->post('getAnalysticsUsage','AnalyticsController@getAnalysticsUsage');
    $router->post('analysticsProgress','AnalyticsController@analysticsProgress');
    $router->post('/analyticsQuestionLog','AnalyticsController@analyticsQuestionLog');
    $router->post('analytics_standard','AnalyticsController@analytics_standard');
    $router->post('analytics_subjects','AnalyticsController@analytics_subjects');
#Learning Part Student
    $router->post('getrecommendations','LearningController@getrecommendations');
    $router->post('getLearningStandardMaths','LearningController@getLearningStandardMaths');
    $router->get('learning_awards','LearningController@learning_awards');
    $router->post('get_subjects','LearningController@get_subjects');

#Analytics part Parent
    $router->get('getStudentsList','ParentController@getStudentsList');
    $router->post('getParentAnalyticsusage','ParentController@getParentAnalyticsusage');
    $router->post('getParentProgress','ParentController@getParentProgress');
    $router->post('kid_profile_update','ParentController@kid_profile_update');
    $router->post('search_results','SubCategoryController@search_results');

#Teachers Part
    $router->post('teacher_login','AuthController@teacher_login');
    $router->get('teacher_dashboard','TeacherController@teacher_dashboard');
    $router->get('teacher_questions_list','TeacherController@teacher_questions_list');
    $router->post('teacher_upload_question', 'TeacherController@teacher_upload_question');
    $router->post('teacher_insert_question', 'TeacherController@teacher_insert_question');
#question Answer Part
    $router->get('questions_list','QuestionAnswerController@getAll');
    $router->get('get_teacher_questions','QuestionAnswerController@get_teacher_questions');
    $router->post('add_question_answer','QuestionAnswerController@add');
    $router->post('update_answer','QuestionAnswerController@update_answer');
});
