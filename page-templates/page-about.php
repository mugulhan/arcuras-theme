<?php
/**
 * Template Name: About Us
 * Description: About Arcuras page template
 *
 * @package Gufte
 */

get_header();
?>

<main id="primary" class="site-main">
    <!-- Hero Section -->
    <section class="about-hero relative bg-gradient-to-br from-purple-600 via-pink-500 to-red-500 text-white py-24">
        <div class="absolute inset-0 bg-black opacity-40"></div>
        <div class="container mx-auto px-4 relative z-10">
            <div class="max-w-4xl mx-auto text-center">
                <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in">About Arcuras</h1>
                <p class="text-xl md:text-2xl opacity-90">Bridging Cultures Through Music</p>
            </div>
        </div>
    </section>

    <!-- Mission Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Mission</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto"></div>
                </div>
                
                <div class="prose prose-lg max-w-none text-gray-600">
                    <p class="text-xl leading-relaxed mb-6">
                        Founded in 2024, <strong>Arcuras</strong> is a non-profit organization dedicated to making music lyrics 
                        accessible and understandable to people worldwide. We believe that music is a universal language that 
                        transcends borders, and understanding the words behind the melodies enriches the human experience.
                    </p>
                    
                    <p class="text-lg leading-relaxed">
                        Our platform serves as a bridge between cultures, allowing music lovers to explore and understand 
                        songs from different languages and regions. Through the generous contributions of native speakers 
                        and professional translators from around the world, we provide accurate translations that preserve 
                        the original meaning, emotion, and cultural context of each song.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- What We Do Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">What We Do</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8 mt-12">
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
                        <div class="text-5xl mb-4">🌍</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Global Accessibility</h3>
                        <p class="text-gray-600">
                            We make song lyrics accessible to people regardless of their native language, 
                            breaking down language barriers in music appreciation.
                        </p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
                        <div class="text-5xl mb-4">🤝</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Community Collaboration</h3>
                        <p class="text-gray-600">
                            Native speakers and translators from different countries contribute their expertise 
                            to ensure accurate and culturally sensitive translations.
                        </p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center hover:shadow-xl transition-shadow">
                        <div class="text-5xl mb-4">🎵</div>
                        <h3 class="text-xl font-bold text-gray-800 mb-3">Cultural Preservation</h3>
                        <p class="text-gray-600">
                            We preserve the cultural context and artistic expression of songs while making 
                            them understandable to a global audience.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Our Values Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Values</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto"></div>
                </div>
                
                <div class="space-y-8">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-purple-600 font-bold">1</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Non-Profit Mission</h3>
                            <p class="text-gray-600">
                                As a non-profit organization, our primary goal is to serve the global community. 
                                We operate without commercial interests, focusing solely on making music more accessible and enjoyable for everyone.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-pink-600 font-bold">2</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Accuracy & Authenticity</h3>
                            <p class="text-gray-600">
                                We prioritize accuracy in translations, working with native speakers to ensure that 
                                the true meaning, emotion, and cultural nuances of lyrics are preserved.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-blue-600 font-bold">3</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Cultural Understanding</h3>
                            <p class="text-gray-600">
                                We believe that understanding songs in their original context promotes cultural 
                                appreciation and brings people from different backgrounds closer together.
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-start">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                            <span class="text-green-600 font-bold">4</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Open Collaboration</h3>
                            <p class="text-gray-600">
                                We welcome contributions from translators, music enthusiasts, and cultural experts 
                                worldwide, creating a collaborative platform that benefits from diverse perspectives.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics Section -->
    <section class="py-16 bg-gradient-to-r from-purple-600 to-pink-600 text-white">
        <div class="container mx-auto px-4">
            <div class="max-w-6xl mx-auto">
                <div class="grid md:grid-cols-4 gap-8 text-center">
                    <?php
                    // Get statistics
                    $total_songs = wp_count_posts('post')->publish;
                    $total_singers = wp_count_terms('singer');
                    $total_albums = wp_count_terms('album');
                    
                    // Count available languages
                    $language_stats = gufte_get_language_statistics();
                    $total_languages = count($language_stats);
                    ?>
                    
                    <div>
                        <div class="text-5xl font-bold mb-2"><?php echo number_format($total_songs); ?></div>
                        <div class="text-xl opacity-90">Song Lyrics</div>
                    </div>
                    
                    <div>
                        <div class="text-5xl font-bold mb-2"><?php echo number_format($total_languages); ?></div>
                        <div class="text-xl opacity-90">Languages</div>
                    </div>
                    
                    <div>
                        <div class="text-5xl font-bold mb-2"><?php echo number_format($total_singers); ?></div>
                        <div class="text-xl opacity-90">Artists</div>
                    </div>
                    
                    <div>
                        <div class="text-5xl font-bold mb-2">∞</div>
                        <div class="text-xl opacity-90">Cultures Connected</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Join Us Section -->
    <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-6">Join Our Mission</h2>
                <p class="text-xl text-gray-600 mb-8">
                    Help us make music accessible to everyone. Whether you're a native speaker, 
                    professional translator, or music enthusiast, your contribution matters.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="https://arcuras.com/become-a-contributor/" 
                   class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white px-8 py-4 rounded-lg font-semibold hover:shadow-lg transform hover:-translate-y-1 transition-all">
                    Become a Contributor
                </a>
                    <a href="<?php echo home_url('/contact'); ?>" 
                       class="inline-block bg-white text-gray-800 px-8 py-4 rounded-lg font-semibold border-2 border-gray-300 hover:border-purple-600 transition-all">
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Timeline Section -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Our Journey</h2>
                    <div class="w-24 h-1 bg-gradient-to-r from-purple-500 to-pink-500 mx-auto"></div>
                </div>
                
                <div class="relative">
                    <!-- Timeline line -->
                    <div class="absolute left-1/2 transform -translate-x-1/2 h-full w-0.5 bg-gray-300"></div>
                    
                    <!-- Timeline items -->
                    <div class="space-y-12">
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <h3 class="text-xl font-bold text-gray-800">Foundation</h3>
                                <p class="text-gray-600">Arcuras was established as a non-profit organization with a vision to connect cultures through music.</p>
                            </div>
                            <div class="relative flex items-center justify-center w-12 h-12 bg-purple-600 rounded-full text-white font-bold">
                                2024
                            </div>
                            <div class="flex-1 pl-8"></div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-1 pr-8"></div>
                            <div class="relative flex items-center justify-center w-12 h-12 bg-pink-600 rounded-full text-white font-bold">
                                Now
                            </div>
                            <div class="flex-1 text-left pl-8">
                                <h3 class="text-xl font-bold text-gray-800">Growing Community</h3>
                                <p class="text-gray-600">Building a global network of contributors and serving millions of music lovers worldwide.</p>
                            </div>
                        </div>
                        
                        <div class="flex items-center">
                            <div class="flex-1 text-right pr-8">
                                <h3 class="text-xl font-bold text-gray-800">Future Vision</h3>
                                <p class="text-gray-600">Expanding to cover more languages and creating educational resources for language learning through music.</p>
                            </div>
                            <div class="relative flex items-center justify-center w-12 h-12 bg-blue-600 rounded-full text-white font-bold">
                                2025+
                            </div>
                            <div class="flex-1 pl-8"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php
get_footer();