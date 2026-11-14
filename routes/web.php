<?php
$router = new Router();

// Public routes
$router->get("/", "HomeController@index", ['guest']);
$router->get("/ai-diagnosis", "AIDiagnosisController@index");
$router->post("/ai-diagnosis/diagnose", "AIDiagnosisController@diagnose");
$router->post("/ai-diagnosis/proceed", "AIDiagnosisController@proceedToOrder");

// Auth routes (only accessible by guests)
$router->get("/login", "AuthController@login", ['guest']);
$router->get("/register", "AuthController@register", ['guest']);
$router->post("/login", "AuthController@loginPost", ['guest']);
$router->post("/register", "AuthController@registerPost", ['guest']);

// Logout route
$router->get("/logout", "AuthController@logout", ['auth']);

// Account & Profile Settings routes (accessible by all authenticated users)
$router->get("/account/settings", "AccountController@index", ['auth']);
$router->post("/account/settings/profile", "AccountController@updateProfile", ['auth']);
$router->post("/account/settings/password", "AccountController@updatePassword", ['auth']);
$router->get("/customer/settings", "AccountController@index", ['auth', 'role:CUSTOMER']);
$router->get("/technician/settings", "AccountController@index", ['auth', 'role:TEKNISI']);
$router->get("/admin/settings", "AccountController@index", ['auth', 'role:ADMIN']);

$router->get("/customer/dashboard", "CustomerController@dashboard", ['auth', 'role:CUSTOMER']);
$router->get("/customer/ai-chat", "AIChatController@index", ['auth', 'role:CUSTOMER|ADMIN|TEKNISI']);
$router->get("/admin/ai-chat", "AIChatController@index", ['auth', 'role:ADMIN']);
$router->get("/technician/ai-chat", "AIChatController@index", ['auth', 'role:TEKNISI']);
$router->get("/ai-chat", "AIChatController@index", ['auth', 'role:CUSTOMER|ADMIN|TEKNISI']);
$router->post("/customer/ai-chat/session/create", "AIChatController@createSession", ['auth', 'role:CUSTOMER|ADMIN|TEKNISI']);
$router->post("/customer/ai-chat/send", "AIChatController@sendMessage", ['auth', 'role:CUSTOMER|ADMIN|TEKNISI']);
$router->post("/customer/ai-chat/session/delete", "AIChatController@deleteSession", ['auth', 'role:CUSTOMER|ADMIN|TEKNISI']);
$router->get("/customer/order", "CustomerOrderController@index", ['auth', 'role:CUSTOMER']);
$router->get("/customer/order/create", "CustomerOrderController@create", ['auth', 'role:CUSTOMER']);
$router->post("/customer/order/store", "CustomerOrderController@store", ['auth', 'role:CUSTOMER']);
$router->get("/customer/order/detail", "CustomerOrderController@show", ['auth', 'role:CUSTOMER']);
$router->get("/customer/order/history", "CustomerOrderController@history", ['auth', 'role:CUSTOMER']);
$router->post("/customer/review/store", "ReviewController@store", ['auth', 'role:CUSTOMER']);
$router->get("/customer/payment", "PaymentController@index", ['auth', 'role:CUSTOMER']);
$router->post("/customer/payment/store", "PaymentController@store", ['auth', 'role:CUSTOMER']);
$router->get("/customer/payment/receipt", "PaymentController@receipt", ['auth', 'role:CUSTOMER']);

// Notification API routes (semua role yang sudah login)
$router->get("/notifications/fetch",        "NotificationController@fetch",       ['auth']);
$router->post("/notifications/mark-all-read","NotificationController@markAllRead", ['auth']);
$router->post("/notifications/mark-read",   "NotificationController@markRead",    ['auth']);
$router->post("/notifications/delete",      "NotificationController@delete",      ['auth']);

$router->get("/admin/dashboard", "AdminController@dashboard", ['auth', 'role:ADMIN']);
$router->get("/admin/orders", "AdminOrderController@index", ['auth', 'role:ADMIN']);
$router->get("/admin/orders/detail", "AdminOrderController@show", ['auth', 'role:ADMIN']);
$router->post("/admin/orders/assign", "AdminOrderController@assignTechnician", ['auth', 'role:ADMIN']);
$router->get("/admin/reviews", "ReviewController@adminIndex", ['auth', 'role:ADMIN']);
$router->get("/admin/reports", "AdminReportController@index", ['auth', 'role:ADMIN']);
$router->get("/admin/ai-setting", "AdminAISettingController@index", ['auth', 'role:ADMIN']);
$router->post("/admin/ai-setting/update", "AdminAISettingController@update", ['auth', 'role:ADMIN']);
$router->post("/admin/ai-setting/fetch-models", "AdminAISettingController@fetchModels", ['auth', 'role:ADMIN']);
$router->post("/admin/ai-setting/test-connection", "AdminAISettingController@testConnection", ['auth', 'role:ADMIN']);

$router->get("/technician/dashboard", "TechnicianController@dashboard", ['auth', 'role:TEKNISI']);
$router->get("/technician/jobs", "TechnicianOrderController@index", ['auth', 'role:TEKNISI']);
$router->get("/technician/jobs/detail", "TechnicianOrderController@show", ['auth', 'role:TEKNISI']);
$router->post("/technician/jobs/status", "TechnicianOrderController@updateStatus", ['auth', 'role:TEKNISI']);
$router->post("/technician/jobs/complete", "TechnicianOrderController@complete", ['auth', 'role:TEKNISI']);

// Admin Master Data Routes
$router->get("/admin/customer", "AdminCustomerController@index", ['auth', 'role:ADMIN']);
$router->get("/admin/customer/create", "AdminCustomerController@create", ['auth', 'role:ADMIN']);
$router->post("/admin/customer/store", "AdminCustomerController@store", ['auth', 'role:ADMIN']);
$router->get("/admin/customer/edit", "AdminCustomerController@edit", ['auth', 'role:ADMIN']);
$router->post("/admin/customer/update", "AdminCustomerController@update", ['auth', 'role:ADMIN']);
$router->post("/admin/customer/delete", "AdminCustomerController@delete", ['auth', 'role:ADMIN']);

$router->get("/admin/technician", "AdminTechnicianController@index", ['auth', 'role:ADMIN']);
$router->get("/admin/technician/create", "AdminTechnicianController@create", ['auth', 'role:ADMIN']);
$router->post("/admin/technician/store", "AdminTechnicianController@store", ['auth', 'role:ADMIN']);
$router->get("/admin/technician/edit", "AdminTechnicianController@edit", ['auth', 'role:ADMIN']);
$router->post("/admin/technician/update", "AdminTechnicianController@update", ['auth', 'role:ADMIN']);
$router->post("/admin/technician/delete", "AdminTechnicianController@delete", ['auth', 'role:ADMIN']);

$router->get("/admin/equipment", "AdminEquipmentController@index", ['auth', 'role:ADMIN']);
$router->get("/admin/equipment/create", "AdminEquipmentController@create", ['auth', 'role:ADMIN']);
$router->post("/admin/equipment/store", "AdminEquipmentController@store", ['auth', 'role:ADMIN']);
$router->get("/admin/equipment/edit", "AdminEquipmentController@edit", ['auth', 'role:ADMIN']);
$router->post("/admin/equipment/update", "AdminEquipmentController@update", ['auth', 'role:ADMIN']);
$router->post("/admin/equipment/delete", "AdminEquipmentController@delete", ['auth', 'role:ADMIN']);

return $router;

