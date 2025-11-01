<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package Arcuras
 */

get_header();
?>

<div class="site-content-wrapper flex flex-col md:flex-row min-h-screen">

    <?php get_template_part('template-parts/arcuras-sidebar'); ?>

    <main id="primary" class="site-main flex-1 overflow-x-hidden">

        <div class="error-404 not-found min-h-screen flex items-center justify-center px-4 py-16 bg-gradient-to-br from-slate-50 via-blue-50/30 to-slate-50">

            <div class="max-w-3xl w-full text-center">

                <!-- 404 Number - Large and Bold -->
                <div class="relative mb-8">
                    <h1 class="text-[150px] md:text-[200px] lg:text-[250px] font-black leading-none tracking-tighter text-slate-900">
                        404
                    </h1>
                    <div class="absolute inset-0 blur-3xl opacity-20 bg-gradient-to-r from-blue-400 to-slate-400 -z-10"></div>
                </div>

                <!-- Title & Description -->
                <div class="mb-12 space-y-4">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900">
                        <?php esc_html_e('Page Not Found', 'gufte'); ?>
                    </h2>
                    <p class="text-lg text-slate-600 max-w-lg mx-auto">
                        <?php esc_html_e('The page you are looking for might have been removed or is temporarily unavailable.', 'gufte'); ?>
                    </p>
                </div>

                <!-- Search Form -->
                <div class="mb-12 max-w-xl mx-auto">
                    <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="relative">
                        <div class="relative flex items-center">
                            <div class="absolute left-5 top-1/2 -translate-y-1/2 pointer-events-none">
                                <?php gufte_icon("magnify", "text-slate-400 w-5 h-5"); ?>
                            </div>
                            <input
                                type="search"
                                name="s"
                                value="<?php echo get_search_query(); ?>"
                                placeholder="<?php esc_attr_e('Search songs, singers, albums...', 'gufte'); ?>"
                                class="w-full pl-14 pr-32 py-4 rounded-full border-2 border-slate-200 focus:border-slate-900 focus:outline-none transition-colors text-slate-900 placeholder:text-slate-400"
                            />
                            <button
                                type="submit"
                                class="absolute right-2 bg-slate-900 hover:bg-slate-800 text-white px-6 py-2.5 rounded-full transition-colors font-medium"
                            >
                                <?php esc_html_e('Search', 'gufte'); ?>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center mb-16">
                    <a
                        href="<?php echo esc_url(home_url('/')); ?>"
                        class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold px-8 py-4 rounded-full transition-all"
                    >
                        <?php gufte_icon("home", "w-5 h-5"); ?>
                        <?php esc_html_e('Back to Home', 'gufte'); ?>
                    </a>

                    <a
                        href="javascript:history.back()"
                        class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-900 font-semibold px-8 py-4 rounded-full border-2 border-slate-900 transition-all"
                    >
                        <?php gufte_icon("arrow-left", "w-5 h-5"); ?>
                        <?php esc_html_e('Go Back', 'gufte'); ?>
                    </a>
                </div>

                <!-- Quick Links -->
                <div class="border-t border-slate-200 pt-12">
                    <p class="text-sm text-slate-500 mb-6 uppercase tracking-wider font-medium">
                        <?php esc_html_e('Quick Links', 'gufte'); ?>
                    </p>

                    <div class="flex flex-wrap gap-3 justify-center">
                        <?php if (taxonomy_exists('singer')) : ?>
                        <a
                            href="<?php echo esc_url(home_url('/singers/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-full border border-slate-200 hover:border-slate-900 transition-all font-medium text-sm"
                        >
                            <?php gufte_icon("microphone", "w-4 h-4"); ?>
                            <?php esc_html_e('Singers', 'gufte'); ?>
                        </a>
                        <?php endif; ?>

                        <?php if (taxonomy_exists('songwriter')) : ?>
                        <a
                            href="<?php echo esc_url(home_url('/songwriters/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-full border border-slate-200 hover:border-slate-900 transition-all font-medium text-sm"
                        >
                            <?php gufte_icon("pen", "w-4 h-4"); ?>
                            <?php esc_html_e('Songwriters', 'gufte'); ?>
                        </a>
                        <?php endif; ?>

                        <?php if (taxonomy_exists('album')) : ?>
                        <a
                            href="<?php echo esc_url(home_url('/albums/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-full border border-slate-200 hover:border-slate-900 transition-all font-medium text-sm"
                        >
                            <?php gufte_icon("album", "w-4 h-4"); ?>
                            <?php esc_html_e('Albums', 'gufte'); ?>
                        </a>
                        <?php endif; ?>

                        <a
                            href="<?php echo esc_url(home_url('/categories/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-full border border-slate-200 hover:border-slate-900 transition-all font-medium text-sm"
                        >
                            <?php gufte_icon("folder-music", "w-4 h-4"); ?>
                            <?php esc_html_e('Categories', 'gufte'); ?>
                        </a>

                        <a
                            href="<?php echo esc_url(home_url('/latest/')); ?>"
                            class="inline-flex items-center gap-2 px-6 py-2.5 bg-white hover:bg-slate-900 text-slate-700 hover:text-white rounded-full border border-slate-200 hover:border-slate-900 transition-all font-medium text-sm"
                        >
                            <?php gufte_icon("clock", "w-4 h-4"); ?>
                            <?php esc_html_e('Latest', 'gufte'); ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>

    </main>
</div>

<?php
get_footer();
