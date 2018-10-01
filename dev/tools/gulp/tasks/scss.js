import gulp from 'gulp';
import sass from 'gulp-sass';
import sourceMaps from 'gulp-sourcemaps';
import ThemeRegistry from '../utils/theme-registry';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);
    if (!themeConfig) {
        throw new Error('Please specify theme after colon.');
    }

    return gulp.src(themeConfig.preprocessorFiles)
        .pipe(sourceMaps.init())
        .pipe(sass().on('error', sass.logError))
        .pipe(sourceMaps.write('.'))
        .pipe(gulp.dest(themeConfig.path + 'css/'));
}
