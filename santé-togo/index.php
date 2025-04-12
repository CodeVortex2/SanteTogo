<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SantéTogo - La santé numérique pour tous</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            scroll-behavior: smooth;
        }

        .hero-gradient {
            background: linear-gradient(135deg, #2563eb 0%, #10b981 100%);
        }

        .feature-card {
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: #2563eb;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.1);
        }

        .pulse-animation {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>
</head>

<body class="text-gray-800 bg-gray-50">

    <!-- Barre de navigation -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fas fa-heartbeat text-3xl text-blue-600 mr-2"></i>
                <a href="#" class="text-2xl font-bold text-blue-600">Santé<span class="text-green-500">Togo</span></a>
            </div>

            <div class="hidden md:flex space-x-8 items-center">
                <a href="#features" class="font-medium hover:text-blue-600 transition">Fonctionnalités</a>
                <a href="#how-it-works" class="font-medium hover:text-blue-600 transition">Comment ça marche</a>
                <a href="#testimonials" class="font-medium hover:text-blue-600 transition">Témoignages</a>
                <a href="login.php"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-full font-medium transition ml-4">
                    <i class="fas fa-sign-in-alt mr-2"></i>Connexion
                </a>
            </div>

            <button class="md:hidden text-2xl focus:outline-none" id="menu-toggle">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Menu mobile -->
        <div class="md:hidden hidden bg-white py-2 px-4" id="mobile-menu">
            <a href="#features" class="block py-2 hover:text-blue-600">Fonctionnalités</a>
            <a href="#how-it-works" class="block py-2 hover:text-blue-600">Comment ça marche</a>
            <a href="#testimonials" class="block py-2 hover:text-blue-600">Témoignages</a>
            <a href="login.php" class="block bg-blue-600 text-white py-2 px-4 rounded-full text-center my-2">
                <i class="fas fa-sign-in-alt mr-2"></i>Connexion
            </a>
        </div>
    </nav>

    <!-- Section Hero -->
    <section class="hero-gradient text-white">
        <div class="container mx-auto px-4 py-20 md:py-28 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2 mb-10 md:mb-0">
                <h1 class="text-4xl md:text-5xl font-bold mb-6 leading-tight">
                    Votre santé, <br><span class="bg-white text-blue-600 px-2 rounded">notre priorité</span>
                </h1>
                <p class="text-xl mb-8 opacity-90">
                    La première plateforme togolaise qui révolutionne l'accès aux soins.
                    Gestion médicale 100% en ligne, sécurisée et intuitive.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="register.php"
                        class="bg-white text-blue-600 hover:bg-gray-100 font-bold py-3 px-8 rounded-full text-center transition shadow-lg pulse-animation">
                        <i class="fas fa-user-plus mr-2"></i>Commencer maintenant
                    </a>
                    <a href="#features"
                        class="border-2 border-white text-white hover:bg-white hover:text-blue-600 font-bold py-3 px-6 rounded-full text-center transition">
                        <i class="fas fa-play-circle mr-2"></i>Voir la démo
                    </a>
                </div>

                <div class="mt-12 flex flex-wrap gap-6 items-center">
                    <div class="flex items-center bg-white/20 px-4 py-2 rounded-full">
                        <i class="fas fa-check-circle mr-2"></i>
                        <span>100% Sécurisé</span>
                    </div>
                    <div class="flex items-center bg-white/20 px-4 py-2 rounded-full">
                        <i class="fas fa-mobile-alt mr-2"></i>
                        <span>Mobile Friendly</span>
                    </div>
                </div>
            </div>

            <div class="md:w-1/2 relative">
                <img src="https://images.unsplash.com/photo-1581056771107-24ca5f033842?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                    alt="Médecin africain souriant"
                    class="rounded-xl shadow-2xl w-full max-w-md mx-auto border-4 border-white">

                <div class="absolute -bottom-5 -right-5 bg-white p-4 rounded-xl shadow-lg hidden md:block">
                    <div class="flex items-center">
                        <div class="bg-green-100 p-3 rounded-full mr-3">
                            <i class="fas fa-heart text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">+500 vies sauvées</p>
                            <p class="text-sm text-gray-600">Grâce à notre plateforme</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Fonctionnalités -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-sm font-semibold mb-3">
                    NOTRE SOLUTION
                </span>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Tout pour votre santé en un seul endroit</h2>
                <p class="text-lg text-gray-600">
                    Découvrez comment SantéTogo simplifie votre parcours médical au quotidien
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Feature 1 -->
                <div class="feature-card bg-white p-6 rounded-xl shadow-sm">
                    <div
                        class="bg-blue-100 text-blue-600 w-16 h-16 rounded-full flex items-center justify-center mb-5 mx-auto">
                        <i class="fas fa-file-medical text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-center">Dossier Médical</h3>
                    <p class="text-gray-600 text-center mb-4">
                        Centralisez tous vos documents de santé en un seul endroit sécurisé
                    </p>
                    <a href="#" class="text-blue-600 font-medium flex items-center justify-center">
                        En savoir plus <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- Feature 2 -->
                <div class="feature-card bg-white p-6 rounded-xl shadow-sm">
                    <div
                        class="bg-green-100 text-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-5 mx-auto">
                        <i class="fas fa-calendar-check text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-center">Prise de RDV</h3>
                    <p class="text-gray-600 text-center mb-4">
                        Prenez rendez-vous avec les meilleurs spécialistes en quelques clics
                    </p>
                    <a href="#" class="text-green-600 font-medium flex items-center justify-center">
                        En savoir plus <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- Feature 3 -->
                <div class="feature-card bg-white p-6 rounded-xl shadow-sm">
                    <div
                        class="bg-purple-100 text-purple-600 w-16 h-16 rounded-full flex items-center justify-center mb-5 mx-auto">
                        <i class="fas fa-pills text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-center">Rappels Médicaments</h3>
                    <p class="text-gray-600 text-center mb-4">
                        Alertes personnalisées pour ne jamais oublier vos traitements
                    </p>
                    <a href="#" class="text-purple-600 font-medium flex items-center justify-center">
                        En savoir plus <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>

                <!-- Feature 4 -->
                <div class="feature-card bg-white p-6 rounded-xl shadow-sm">
                    <div
                        class="bg-red-100 text-red-600 w-16 h-16 rounded-full flex items-center justify-center mb-5 mx-auto">
                        <i class="fas fa-heart text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3 text-center">Don de Sang</h3>
                    <p class="text-gray-600 text-center mb-4">
                        Sauvez des vies en vous connectant aux centres de don près de chez vous
                    </p>
                    <a href="#" class="text-red-600 font-medium flex items-center justify-center">
                        En savoir plus <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Section Comment ça marche -->
    <section id="how-it-works" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block bg-blue-100 text-blue-600 px-4 py-1 rounded-full text-sm font-semibold mb-3">
                    SIMPLE ET RAPIDE
                </span>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Comment utiliser SantéTogo</h2>
                <p class="text-lg text-gray-600">
                    Commencez en seulement 3 étapes faciles
                </p>
            </div>

            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 order-2 lg:order-1">
                    <div class="space-y-8">
                        <!-- Step 1 -->
                        <div class="flex items-start gap-6 p-5 bg-white rounded-xl shadow-sm">
                            <div
                                class="flex-shrink-0 bg-blue-600 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold text-lg mt-1">
                                1
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Créez votre compte</h3>
                                <p class="text-gray-600">
                                    Inscrivez-vous en 2 minutes avec votre email et un mot de passe sécurisé
                                </p>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="flex items-start gap-6 p-5 bg-white rounded-xl shadow-sm">
                            <div
                                class="flex-shrink-0 bg-green-600 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold text-lg mt-1">
                                2
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Complétez votre profil</h3>
                                <p class="text-gray-600">
                                    Ajoutez vos informations médicales importantes pour un suivi personnalisé
                                </p>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="flex items-start gap-6 p-5 bg-white rounded-xl shadow-sm">
                            <div
                                class="flex-shrink-0 bg-purple-600 text-white rounded-full w-12 h-12 flex items-center justify-center font-bold text-lg mt-1">
                                3
                            </div>
                            <div>
                                <h3 class="text-xl font-bold mb-2">Utilisez les services</h3>
                                <p class="text-gray-600">
                                    Accédez à toutes les fonctionnalités selon vos besoins de santé
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 text-center lg:text-left">
                        <a href="register.php" class="inline-flex items-center text-blue-600 font-bold text-lg">
                            Commencez maintenant <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                </div>

                <div class="lg:w-1/2 order-1 lg:order-2">
                    <img src="https://images.unsplash.com/photo-1551601651-bc60f254d532?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=800&q=80"
                        alt="Femme africaine utilisant smartphone" class="rounded-xl shadow-xl w-full">
                </div>
            </div>
        </div>
    </section>

    <!-- Section Témoignages -->
    <section id="testimonials" class="py-20 bg-blue-600 text-white">
        <div class="container mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block bg-white/20 px-4 py-1 rounded-full text-sm font-semibold mb-3">
                    CE QU'ILS DISENT DE NOUS
                </span>
                <h2 class="text-3xl md:text-4xl font-bold mb-4">Ils ont adopté SantéTogo</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Témoignage 1 -->
                <div class="bg-white/10 p-8 rounded-xl backdrop-blur-sm">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80"
                            alt="Patient" class="w-16 h-16 rounded-full object-cover border-2 border-white">
                        <div class="ml-4">
                            <h4 class="font-bold text-lg">Koffi A.</h4>
                            <div class="flex text-yellow-300 mt-1">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                    </div>
                    <p class="italic">
                        "SantéTogo a changé ma façon de gérer ma santé. Je peux maintenant accéder à tous mes documents
                        médicaux n'importe où, c'est révolutionnaire !"
                    </p>
                </div>

                <!-- Témoignage 2 -->
                <div class="bg-white/10 p-8 rounded-xl backdrop-blur-sm">
                    <div class="flex items-center mb-6">
                        <img src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=200&q=80"
                            alt="Médecin" class="w-16 h-16 rounded-full object-cover border-2 border-white">
                        <div class="ml-4">
                            <h4 class="font-bold text-lg">Dr. Amé D.</h4>
                            <div class="flex text-yellow-300 mt-1">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star-half-alt"></i>
                            </div>
                        </div>
                    </div>
                    <p class="italic">
                        "En tant que médecin, cette plateforme m'a permis de gagner un temps précieux dans la gestion de
                        mes patients. Les dossiers sont complets et accessibles en un clic."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Final -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div
                class="bg-gradient-to-r from-blue-600 to-green-500 rounded-2xl p-8 md:p-12 text-center text-white shadow-xl">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">Prêt à prendre le contrôle de votre santé ?</h2>
                <p class="text-lg mb-8 max-w-2xl mx-auto">
                    Rejoignez des milliers de Togolais qui simplifient déjà leur parcours médical avec SantéTogo
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="register.php"
                        class="bg-white text-blue-600 hover:bg-gray-100 font-bold py-4 px-8 rounded-full text-center transition shadow-lg">
                        <i class="fas fa-user-plus mr-2"></i>S'inscrire gratuitement
                    </a>
                    <a href="#features"
                        class="border-2 border-white text-white hover:bg-white/10 font-bold py-4 px-8 rounded-full text-center transition">
                        <i class="fas fa-info-circle mr-2"></i>En savoir plus
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Pied de page -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <div>
                    <div class="flex items-center mb-4">
                        <i class="fas fa-heartbeat text-3xl text-blue-400 mr-2"></i>
                        <span class="text-2xl font-bold">Santé<span class="text-green-400">Togo</span></span>
                    </div>
                    <p class="text-gray-400 mb-4">
                        La plateforme numérique qui révolutionne l'accès aux soins au Togo
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-white text-xl"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-4">Navigation</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Accueil</a></li>
                        <li><a href="#features" class="text-gray-400 hover:text-white transition">Fonctionnalités</a>
                        </li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition">Comment ça
                                marche</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-white transition">Témoignages</a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-4">Services</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Dossier Médical</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Prise de RDV</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Rappels Médicaments</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition">Don de Sang</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-lg font-bold mb-4">Contact</h4>
                    <ul class="space-y-2">
                        <li class="flex items-center text-gray-400"><i class="fas fa-map-marker-alt mr-3"></i> Lomé,
                            Togo</li>
                        <li class="flex items-center text-gray-400"><i class="fas fa-phone-alt mr-3"></i> +228 22 22 22
                            22</li>
                        <li class="flex items-center text-gray-400"><i class="fas fa-envelope mr-3"></i>
                            contact@sante-togo.tg</li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-8 text-center text-gray-400">
                <p>© 2023 SantéTogo. Tous droits réservés.</p>
            </div>
        </div>
    </footer>

    <!-- Bouton Retour en haut -->
    <button id="back-to-top"
        class="fixed bottom-8 right-8 bg-blue-600 text-white p-3 rounded-full shadow-lg opacity-0 invisible transition-all duration-300 hover:bg-blue-700">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Menu mobile
        document.getElementById('menu-toggle').addEventListener('click', function () {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Back to top button
        const backToTopButton = document.getElementById('back-to-top');

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.remove('opacity-0', 'invisible');
                backToTopButton.classList.add('opacity-100', 'visible');
            } else {
                backToTopButton.classList.remove('opacity-100', 'visible');
                backToTopButton.classList.add('opacity-0', 'invisible');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Animation au scroll
        const animateOnScroll = () => {
            const elements = document.querySelectorAll('.feature-card, .testimonial');

            elements.forEach(element => {
                const elementPosition = element.getBoundingClientRect().top;
                const screenPosition = window.innerHeight / 1.2;

                if (elementPosition < screenPosition) {
                    element.classList.add('animate-fadeInUp');
                }
            });
        };

        window.addEventListener('scroll', animateOnScroll);
        window.addEventListener('load', animateOnScroll);
    </script>
</body>

</html>