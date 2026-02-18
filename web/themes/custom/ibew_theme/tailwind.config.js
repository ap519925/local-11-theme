module.exports = {
    content: [
        './templates/**/*.html.twig',
        './src/js/**/*.js',
        './*.theme'
    ],
    corePlugins: {
        preflight: false, // Disable reset to avoid conflicts with Bootstrap
    },
    theme: {
        extend: {
            colors: {
                'ibew-navy': '#313F6B',
                'ibew-gold': '#CFA655',
                'ibew-cream': '#E8DFC1',
                'ibew-raspberry': '#983A54',
                'ibew-orange': '#E27859',
            },
            fontFamily: {
                'oswald': ['Oswald', 'sans-serif'],
                'sans': ['"Open Sans"', 'sans-serif'],
                'serif': ['Merriweather', 'serif'],
            },
        },
    },
    plugins: [],
}
