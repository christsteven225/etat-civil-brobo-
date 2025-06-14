<?php
//
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="x-icon" href="../public/image/icone2.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Gestion Des Actes D'Etat Civil</title>
    <script src="https://cdn.tailwindcss.com/3.4.16"></script>
    <script>tailwind.config={theme:{extend:{colors:{primary:'#3b82f6',secondary:'#64748b'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.5.0/echarts.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter&amp;display=swap" rel="stylesheet"/>
<style>
        :where([class^="ri-"])::before { content: "\f3c2"; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
        .custom-checkbox {
            position: relative;
            display: inline-block;
            width: 20px;
            height: 20px;
            background-color: white;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .custom-checkbox.checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
        }
        .custom-checkbox.checked::after {
            content: '';
            position: absolute;
            top: 3px;
            left: 6px;
            width: 6px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 48px;
            height: 24px;
        }
        .custom-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .switch-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e5e7eb;
            transition: .4s;
            border-radius: 24px;
        }
        .switch-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .switch-slider {
            background-color: #3b82f6;
        }
        input:checked + .switch-slider:before {
            transform: translateX(24px);
        }
    </style>
</head>
<body>
   <!-- Assistant Modal -->
 
   <div id="assistantModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="fixed bottom-0 right-0 mb-4 mr-4 bg-white rounded-lg shadow-xl w-full max-w-xs sm:max-w-sm md:max-w-md lg:w-96 overflow-hidden">
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 flex justify-between items-center bg-primary text-white">
            <h3 class="text-sm sm:text-base font-medium">Assistant Virtuel</h3>
            <button onclick="toggleAssistant()" class="text-white hover:text-gray-200">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <div class="p-4 sm:p-6 h-64 sm:h-80 md:h-96 overflow-y-auto" id="chatMessages">
            <div class="flex flex-col space-y-3 sm:space-y-4">
                <div class="flex items-start">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-primary flex items-center justify-center text-white mr-2 sm:mr-3">
                        <i class="ri-customer-service-2-line text-sm sm:text-base"></i>
                    </div>
                    <div class="bg-gray-100 rounded-lg p-2 sm:p-3 max-w-[80%] text-xs sm:text-sm">
                        <p>Bonjour ! Je suis votre assistant virtuel. Comment puis-je vous aider concernant vos demandes ou questions à la mairie de Brobo ?</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="px-4 sm:px-6 py-3 sm:py-4 border-t border-gray-200 bg-white">
            <form id="assistantForm" class="flex space-x-2">
                <input type="text" id="messageInput" placeholder="Écrivez votre message..." class="flex-1 px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                <button type="submit" class="bg-primary text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg hover:bg-blue-600 transition whitespace-nowrap text-sm">
                    <i class="ri-send-plane-fill"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Assistant Button -->
<button onclick="toggleAssistant()" class="fixed bottom-4 right-4 bg-primary text-white w-12 h-12 sm:w-14 sm:h-14 rounded-full shadow-lg flex items-center justify-center hover:bg-blue-600 transition z-40">
    <i class="ri-customer-service-2-line text-lg sm:text-xl"></i>
</button>

<!-- Authentication Modal -->
<div id="authModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-xs sm:max-w-sm md:max-w-md mx-auto">
        <div class="flex">
            <button onclick="switchAuthTab('login')" id="loginTab" class="flex-1 px-4 py-2 sm:px-6 sm:py-3 text-center text-xs sm:text-sm font-medium border-b-2 border-primary text-primary">Se connecter</button>
            <button onclick="switchAuthTab('register')" id="registerTab" class="flex-1 px-4 py-2 sm:px-6 sm:py-3 text-center text-xs sm:text-sm font-medium border-b-2 border-gray-200 text-gray-500">S'inscrire</button>
        </div>
        <!-- Login Form -->
        <div id="loginForm" class="p-4 sm:p-6">
            <form action="" method="POST">
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                </div>
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                    <a href="#" class="text-xs sm:text-sm text-primary hover:text-blue-600 mt-1 inline-block">Mot de passe oublié ?</a>
                </div>
                <button type="submit" class="w-full bg-primary text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium hover:bg-blue-600 transition whitespace-nowrap text-xs sm:text-sm">Se connecter</button>
            </form>
            <div class="mt-4 sm:mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-xs sm:text-sm">
                        <span class="px-2 bg-white text-gray-500">Ou continuer avec</span>
                    </div>
                </div>
                <div class="mt-4 sm:mt-6 grid grid-cols-2 gap-2 sm:gap-3">
                    <button class="flex items-center justify-center px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg hover:bg-gray-50 whitespace-nowrap text-xs sm:text-sm">
                        <i class="ri-google-fill mr-1 sm:mr-2 text-red-500 text-sm sm:text-base"></i>
                        Google
                    </button>
                    <button class="flex items-center justify-center px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg hover:bg-gray-50 whitespace-nowrap text-xs sm:text-sm">
                        <i class="ri-facebook-fill mr-1 sm:mr-2 text-blue-600 text-sm sm:text-base"></i>
                        Facebook
                    </button>
                </div>
            </div>
        </div>
        <!-- Register Form -->
        <div id="registerForm" class="p-4 sm:p-6 hidden">
            <form action="" method="POST">
                <div class="grid grid-cols-2 gap-2 sm:gap-4 mb-3 sm:mb-4">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Nom</label>
                        <input type="text" name="lastName" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                    </div>
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Prénom</label>
                        <input type="text" name="firstName" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                    </div>
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Email</label>
                    <input type="email" name="email" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                </div>
                <div class="mb-3 sm:mb-4">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Mot de passe</label>
                    <input type="password" name="password" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                </div>
                <div class="mb-4 sm:mb-6">
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-1 sm:mb-2">Confirmer le mot de passe</label>
                    <input type="password" name="confirmPassword" required class="w-full px-3 sm:px-4 py-1 sm:py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-xs sm:text-sm">
                </div>
                <div class="mb-4 sm:mb-6">
                    <label class="flex items-start">
                        <input type="checkbox" required class="mr-2 mt-0.5 sm:mt-1">
                        <span class="text-xs sm:text-sm text-gray-600">J'accepte les <a href="#" class="text-primary hover:text-blue-600">conditions</a> et la <a href="#" class="text-primary hover:text-blue-600">politique</a></span>
                    </label>
                </div>
                <button type="submit" class="w-full bg-primary text-white px-4 sm:px-6 py-2 sm:py-3 rounded-lg font-medium hover:bg-blue-600 transition whitespace-nowrap text-xs sm:text-sm">S'inscrire</button>
            </form>
        </div>
        <button onclick="closeAuthModal()" class="absolute top-1 sm:top-2 right-1 sm:right-2 text-gray-500 hover:text-gray-700">
            <i class="ri-close-line text-lg sm:text-xl"></i>
        </button>
    </div>
</div>

    <!-- En-tête avec navigation -->
     <header class="bg-white shadow-sm sticky top-0 z-50">
    <!-- Bandeau supérieur -->
    <div class="bg-[#FF6B00] text-white py-2">
        <div class="container mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/f/fe/Flag_of_C%C3%B4te_d%27Ivoire.svg/20px-Flag_of_C%C3%B4te_d%27Ivoire.svg.png" 
                     alt="Drapeau" 
                     class="w-6 h-4 mr-2">
                <span class="text-xs sm:text-sm font-medium">RÉPUBLIQUE DE CÔTE D'IVOIRE</span>
            </div>
            <div class="flex items-center">
                <span class="text-xs sm:text-sm">Union - Discipline - Travail</span>
            </div>
        </div>
    </div>

    <!-- Navigation principale -->
    <div class="container mx-auto px-4 py-3 sm:py-4 flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center">
            <div>
                <h1 class="text-xl sm:text-2xl font-['Pacifico'] text-primary">BROBO</h1>
                <p class="text-gray-600 text-xs sm:text-sm">Commune de BROBO</p>
            </div>
        </div>

        <!-- Menu Desktop (visible à partir de md:768px) -->
        <nav class="hidden md:flex space-x-6">
        <a href="../View/index.php" class="text-primary font-medium">Accueil</a>
        <a href="../View/connexion_decla.php" class="text-gray-600 hover:text-primary transition">Faire une Declaration</a>
        <a href="../View/connexion_citoyen.php" class="text-gray-600 hover:text-primary transition">Demande d'actes</a>
        <a href="../View/citoyen_login.php" class="text-gray-600 hover:text-primary transition">Mes demandes</a>
        <a href="../admin/admin_notice.html" class="text-gray-600 hover:text-primary transitioition">Tableau de bord</a>
        <a href="../View/contact.html" class="text-gray-600 hover:text-primary transition">Contact</a>
      </nav>

        <!-- Bouton Menu Mobile (visible en dessous de md:768px) -->
        <button id="menuToggle" class="md:hidden text-primary focus:outline-none">
            <i class="ri-menu-line text-2xl"></i>
        </button>
    </div>

    <!-- Menu Mobile (masqué par défaut) -->
    <div id="mobileMenu" class="md:hidden hidden bg-white border-t border-gray-200 w-full px-4 py-3 shadow-lg">
    <div class="container mx-auto px-4 py-2 flex flex-col space-y-3">
        <a href="../View/index.php" class="py-2 text-primary font-medium">Accueil</a>
        <a href="../View/connexion_decla.php" class="py-2 text-gray-600 hover:text-primary">Faire une Declaration</a>
        <a href="../View/citoyen_connexion.php" class="py-2 text-gray-600 hover:text-primary">Demande d'actes</a>
        <a href="../View/citoyen_login.php" class="py-2 text-gray-600 hover:text-primary">Mes demandes</a>
        <a href="../admin/admin_notice.html" class="py-2 text-gray-600 hover:text-primary">Tableau de bord</a>
        <a href="../View/contact.html" class="py-2 text-gray-600 hover:text-primary">Contact</a>
      </div>
    </div>
</header>

<!-- Hero section responsive -->
<section class="relative bg-gradient-to-r from-blue-50 to-blue-100 overflow-hidden">
    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Modern%20government%20office%20with%20digital%20services%2C%20clean%20minimalist%20environment%20with%20soft%20blue%20tones%2C%20people%20using%20digital%20kiosks%20for%20administrative%20services%2C%20light%20streaming%20through%20large%20windows%2C%20professional%20atmosphere%2C%20high-quality%20architectural%20details&width=1600&height=800&seq=1&orientation=landscape'); opacity: 0.15;"></div>
    <div class="container mx-auto px-4 sm:px-6 py-12 sm:py-16 md:py-20 relative z-10">
        <div class="max-w-3xl">
            <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mb-4 sm:mb-6">Plateforme de Gestion des Actes d'État Civil
                <br>La plateforme dispose de 3 actes disponibles pour le moment.
            </h1>
            <p class="text-lg sm:text-xl text-gray-700 mb-6 sm:mb-8">Simplifiez vos démarches administratives en ligne. Demandez, payez et recevez vos actes d'état civil sans vous déplacer.</p>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4">
                <a href="../View/connexion_citoyen.php" class="bg-primary text-white px-5 sm:px-6 py-2 sm:py-3 font-medium rounded-lg shadow-lg hover:bg-blue-600 transition whitespace-nowrap text-center">Commencer une demande</a>
                <a href="../View/contact.html" class="bg-white text-gray-800 px-5 sm:px-6 py-2 sm:py-3 font-medium rounded-lg shadow border border-gray-200 hover:bg-gray-50 transition whitespace-nowrap text-center">En savoir plus</a>
            </div>
        </div>
    </div>
</section>

<!-- Services principaux responsive -->
<section class="py-12 sm:py-16 bg-white">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="text-center mb-8 sm:mb-12">
            <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-3 sm:mb-4">Nos Services</h2>
            <p class="text-gray-600 max-w-2xl mx-auto text-sm sm:text-base">Accédez à tous les services d'état civil en quelques clics, sans file d'attente et avec un suivi en temps réel de vos demandes.</p>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Acte de naissance -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Birth%20certificate%20document%20with%20official%20stamps%20and%20seals%2C%20modern%20minimalist%20design%2C%20soft%20blue%20background%2C%20professional%20government%20document%20layout%2C%20high%20quality%20paper%20texture&width=600&height=400&seq=2&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-file-user-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Acte de Naissance</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Demandez votre acte de naissance en ligne et recevez-le directement par email sous format PDF sécurisé.</p>
                    <a href="../View/connexion_citoyen.php" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Faire une demande
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Acte de mariage -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Marriage%20certificate%20with%20elegant%20design%2C%20official%20document%20with%20seals%20and%20signatures%2C%20professional%20layout%2C%20soft%20blue%20background%2C%20high%20quality%20paper%20texture%2C%20government%20document&width=600&height=400&seq=3&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-heart-2-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Acte de Mariage</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Obtenez votre acte de mariage certifié pour vos démarches administratives en quelques étapes simples.</p>
                    <a href="../View/connexion_citoyen.php" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Faire une demande
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Acte de décès -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Death%20certificate%20document%20with%20respectful%20design%2C%20official%20stamps%20and%20seals%2C%20professional%20government%20document%20layout%2C%20soft%20blue%20background%2C%20high%20quality%20paper%20texture&width=600&height=400&seq=4&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-file-list-3-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Acte de Décès</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Simplifiez les démarches administratives lors de moments difficiles avec notre service en ligne sécurisé.</p>
                    <a href="../View/connexion_citoyen.php" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Faire une demande
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>     
     <!-- Suivi des demandes responsive -->
<section class="py-12 sm:py-16 bg-white">
    <div class="container mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
            <!-- Suivi des demandes -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Digital%20tracking%20dashboard%20for%20administrative%20requests%2C%20modern%20UI%20design%20with%20progress%20bars%20and%20status%20indicators%2C%20professional%20interface%20with%20soft%20blue%20tones%2C%20clean%20layout&width=600&height=400&seq=5&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-search-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Suivi des Demandes</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Suivez en temps réel l'état d'avancement de vos demandes d'actes et recevez des notifications à chaque étape.</p>
                    <a href="View/connexion_citoyen.php" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Consulter mes demandes
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Paiement en ligne -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Secure%20online%20payment%20interface%20for%20government%20services%2C%20professional%20payment%20gateway%20with%20credit%20card%20and%20digital%20payment%20options%2C%20clean%20design%20with%20soft%20blue%20background%2C%20modern%20UI&width=600&height=400&seq=6&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-bank-card-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Paiement en Ligne</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Payez vos frais de timbre et autres taxes directement en ligne via notre plateforme sécurisée de paiement.</p>
                    <a href="View/connexion_citoyen.php" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Effectuer un paiement
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Assistance -->
            <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition overflow-hidden border border-gray-100">
                <div class="h-40 sm:h-48 bg-cover bg-center" style="background-image: url('https://readdy.ai/api/search-image?query=Customer%20support%20service%20for%20government%20administration%2C%20professional%20help%20desk%20with%20digital%20assistance%20tools%2C%20modern%20office%20environment%20with%20soft%20blue%20tones%2C%20people%20helping%20citizens&width=600&height=400&seq=7&orientation=landscape')"></div>
                <div class="p-4 sm:p-6">
                    <div class="w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-full bg-blue-100 mb-3 sm:mb-4">
                        <i class="ri-customer-service-2-line text-primary text-lg sm:text-xl"></i>
                    </div>
                    <h3 class="text-lg sm:text-xl font-semibold mb-2">Assistance</h3>
                    <p class="text-gray-600 text-sm sm:text-base mb-3 sm:mb-4">Besoin d'aide ? Notre équipe est disponible pour vous accompagner dans toutes vos démarches administratives.</p>
                    <a href="View/contact.html" class="text-primary font-medium flex items-center text-sm sm:text-base">
                        Contacter l'assistance
                        <i class="ri-arrow-right-line ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<footer class="bg-[#1c2735] text-[#a0a9b8]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col md:flex-row flex-wrap gap-8 md:gap-4 lg:gap-0">
        <!-- Logo et description -->
        <div class="w-full md:w-1/2 lg:w-1/4 space-y-4">
            <div class="text-white font-script text-2xl select-none" style="font-family: 'Pacifico', cursive;">
                BROBO
            </div>
            <p class="text-sm leading-relaxed max-w-[220px]">
                Plateforme officielle de gestion des actes d'état civil de la commune de BroBo.
            </p>
            <div class="flex space-x-4">
                <a aria-label="Facebook" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                    <i class="fab fa-facebook-f text-[#a0a9b8] text-sm"></i>
                </a>
                <a aria-label="Twitter" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                    <i class="fab fa-twitter text-[#a0a9b8] text-sm"></i>
                </a>
                <a aria-label="Instagram" class="w-8 h-8 rounded-full bg-[#2f3a4a] flex items-center justify-center hover:bg-[#3b4a6b] transition-colors" href="#">
                    <i class="fab fa-instagram text-[#a0a9b8] text-sm"></i>
                </a>
            </div>
        </div>

        <!-- Liens rapides -->
        <div class="w-1/2 md:w-1/4 lg:w-1/5 space-y-2">
            <h3 class="text-white font-semibold text-sm mb-3">Liens rapides</h3>
            <ul class="space-y-2 text-sm">
                <li><a class="hover:text-white transition-colors" href="../View/index.php">Accueil</a></li>
                <li><a class="hover:text-white transition-colors" href="../View/connexion_citoyen.php">Demande d'actes</a></li>
                <li><a class="hover:text-white transition-colors" href="../View/citoyen_login.php">Mes demandes</a></li>
                <li><a class="hover:text-white transition-colors" href="../admin/admin_notice.html">Tableau de bord</a></li>
                <li><a class="hover:text-white transition-colors" href="../View/contact.html">Contact</a></li>
            </ul>
        </div>

        <!-- Informations -->
        <div class="w-1/2 md:w-1/4 lg:w-1/5 space-y-2">
            <h3 class="text-white font-semibold text-sm mb-3">Informations</h3>
            <ul class="space-y-2 text-sm">
                <li><a class="hover:text-white transition-colors" href="#">À propos de</a></li>
                <li><a class="hover:text-white transition-colors" href="#">Mentions légales</a></li>
                <li><a class="hover:text-white transition-colors" href="#">Politique de confidentialité</a></li>
                <li><a class="hover:text-white transition-colors" href="#">Conditions d'utilisation</a></li>
                <li><a class="hover:text-white transition-colors" href="#">Plan du site</a></li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="w-full md:w-full lg:w-1/4 space-y-2 text-sm">
            <h3 class="text-white font-semibold text-sm mb-3">Contact</h3>
            <ul class="space-y-3">
                <li class="flex items-start gap-2">
                    <i class="fas fa-map-marker-alt mt-1 text-[#a0a9b8]"></i>
                    <span>Quatier Mairie, BroBo, Côte d'Ivoire</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-phone-alt text-[#a0a9b8]"></i>
                    <span>+225 27 30 64 00 00</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-envelope text-[#a0a9b8]"></i>
                    <span>contact@mairie-brobo.ci</span>
                </li>
                <li class="flex items-center gap-2">
                    <i class="fas fa-clock text-[#a0a9b8]"></i>
                    <span>Lun-Ven : 8h30-17h30</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bas de footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 border-t border-[#2f3a4a] flex flex-col md:flex-row justify-between items-center text-xs text-[#6b7587]">
        <div class="mb-3 md:mb-0">
            © 2025 La commune de BroBo. Tous droits réservés.
        </div>
        <div class="flex items-center space-x-4 md:space-x-6">
            <img alt="MTN CI logo" class="h-6 w-auto" height="24" src="../public/image/MTN.png" width="60"/>
            <img alt="Orange CI logo" class="h-6 w-auto" height="24" src="../public/image/ORANGE.png" width="60"/>
            <img alt="Moov CI logo" class="h-6 w-auto" height="24" src="../public/image/MOOV.png" width="60"/>
        </div>
    </div>

    <!-- Bouton support (positionné en dehors du flux normal) -->
    <button aria-label="Support chat" class="fixed bottom-6 right-6 bg-[#4a90e2] w-12 h-12 rounded-full flex items-center justify-center shadow-lg hover:bg-[#357abd] transition-colors z-50">
        <i class="fas fa-headset text-white text-lg"></i>
    </button>
</footer>
 <!-- Success Message Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-auto p-6 sm:p-8">
        <div class="text-center">
            <!-- Icône de succès - Taille adaptative -->
            <div class="w-12 h-12 sm:w-16 sm:h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3 sm:mb-4">
                <i class="ri-check-line text-green-600 text-2xl sm:text-3xl"></i>
            </div>
            
            <!-- Titre - Taille de police adaptative -->
            <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3">Message envoyé avec succès</h3>
            
            <!-- Message - Marge et taille adaptatives -->
            <p class="text-gray-600 text-sm sm:text-base mb-4 sm:mb-6">
                Nous avons bien reçu votre message. Notre équipe vous répondra dans les plus brefs délais.
            </p>
            
            <!-- Bouton - Taille et padding adaptatifs -->
            <button onclick="closeSuccessModal()" 
                    class="bg-primary text-white px-4 py-2 sm:px-6 sm:py-2 rounded-lg font-medium hover:bg-blue-600 transition whitespace-nowrap text-sm sm:text-base">
                Fermer
            </button>
        </div>
    </div>
</div>
<script>
// Assistant functions - Optimisé pour le footer
function toggleAssistant() {
    const modal = document.getElementById('assistantModal');
    modal.classList.toggle('hidden');
    document.body.classList.toggle('overflow-hidden');
}

function addMessage(message, isUser = false) {
    const chatMessages = document.getElementById('chatMessages');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${isUser ? 'justify-end' : 'justify-start'} mb-4`;
    
    const messageContent = document.createElement('div');
    messageContent.className = `${isUser ? 'bg-primary text-white' : 'bg-gray-100 text-gray-800'} rounded-lg p-3 max-w-xs sm:max-w-md md:max-w-lg`;
    messageContent.innerHTML = `<p class="text-sm sm:text-base">${message}</p>`;
    
    if (!isUser) {
        const iconDiv = document.createElement('div');
        iconDiv.className = 'w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center mr-3';
        iconDiv.innerHTML = '<i class="ri-customer-service-2-line"></i>';
        messageDiv.appendChild(iconDiv);
    }
    
    messageDiv.appendChild(messageContent);
    
    if (isUser) {
        const iconDiv = document.createElement('div');
        iconDiv.className = 'w-8 h-8 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center ml-3';
        iconDiv.innerHTML = '<i class="ri-user-line"></i>';
        messageDiv.appendChild(iconDiv);
    }
    
    chatMessages.appendChild(messageDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Mobile menu - Optimisé pour la navigation
document.getElementById('menuToggle')?.addEventListener('click', function() {
    const menu = document.getElementById('mobileMenu');
    menu.classList.toggle('hidden');
    document.body.classList.toggle('overflow-hidden');
    
    const icon = this.querySelector('i');
    icon.classList.toggle('ri-menu-line');
    icon.classList.toggle('ri-close-line');
});

// Auth Modal functions - Cohérent avec le footer
function openAuthModal(type = 'login') {
    const modal = document.getElementById('authModal');
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    switchAuthTab(type);
}

function closeAuthModal() {
    const modal = document.getElementById('authModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function switchAuthTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(el => {
        el.classList.toggle('border-primary', el.id === `${tab}Tab`);
        el.classList.toggle('text-primary', el.id === `${tab}Tab`);
        el.classList.toggle('border-gray-200', el.id !== `${tab}Tab`);
        el.classList.toggle('text-gray-500', el.id !== `${tab}Tab`);
    });
    
    document.getElementById('loginForm').classList.toggle('hidden', tab !== 'login');
    document.getElementById('registerForm').classList.toggle('hidden', tab !== 'register');
}

// Success modal - Version améliorée
function showSuccessModal(message = 'Nous avons bien reçu votre message. Notre équipe vous répondra dans les plus brefs délais.') {
    const modal = document.getElementById('successModal');
    const messageElement = modal.querySelector('p.text-gray-600');
    if (messageElement) messageElement.textContent = message;
    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// FAQ toggle - Plus accessible
function toggleFaq(button) {
    const content = button.nextElementSibling;
    const icon = button.querySelector('i');
    const isExpanded = content.classList.toggle('hidden');
    
    icon.style.transform = isExpanded ? 'rotate(0)' : 'rotate(180deg)';
    button.setAttribute('aria-expanded', !isExpanded);
}

// Contact form handling - Avec validation
function handleContactFormSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const privacyCheckbox = form.querySelector('#privacyCheckbox');
    
    if (!privacyCheckbox?.classList.contains('checked')) {
        showSuccessModal('Veuillez accepter la politique de confidentialité');
        return;
    }
    
    const formData = new FormData(form);
    console.log('Form submitted:', Object.fromEntries(formData));
    
    // Simulation d'envoi
    setTimeout(() => {
        showSuccessModal();
        form.reset();
        privacyCheckbox.classList.remove('checked');
    }, 500);
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    // Assistant chat
    document.getElementById('assistantForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('messageInput');
        const message = input.value.trim();
        
        if (message) {
            addMessage(message, true);
            input.value = '';
            
            // Réponse simulée avec délai variable
            setTimeout(() => {
                const responses = [
                    "Merci pour votre message concernant la mairie de Brobo. Comment puis-je vous aider?",
                    "Pour toute demande d'acte d'état civil, vous pouvez visiter notre plateforme.",
                    "Nos horaires d'ouverture sont du lundi au vendredi de 8h30 à 17h30."
                ];
                addMessage(responses[Math.floor(Math.random() * responses.length)]);
            }, 800 + Math.random() * 700);
        }
    });

    // Contact form
    document.getElementById('contactForm')?.addEventListener('submit', handleContactFormSubmit);
    
    // Gestion des clics extérieurs
    document.addEventListener('click', function(event) {
        // Notifications dropdown
        const notifDropdown = document.getElementById('notificationsDropdown');
        if (notifDropdown && !event.target.closest('#notificationsDropdown, [aria-label="Notifications"]')) {
            notifDropdown.classList.add('hidden');
        }
        
        // Fermeture modales
        if (event.target.classList.contains('fixed') && event.target.id !== 'successModal') {
            closeSuccessModal();
            closeAuthModal();
        }
    });
});
</script>
</body>
</html>