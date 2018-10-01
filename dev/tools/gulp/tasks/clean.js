import rimraf from 'gulp-rimraf';
import gulp from 'gulp';
import config from '../config';
import ThemeRegistry from '../utils/theme-registry';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);

    if (themeConfig) {
        return gulp.src(`${config.projectPath}pub/static/${themeConfig.area}/${themeConfig.name}`, {read: false})
            .pipe(rimraf({force: true}));
    }

    return gulp.src([
        config.projectPath + 'pub/static/*',
        '!' + config.projectPath + 'pub/static/.htaccess'
    ], {read: false}).pipe(rimraf({force: true}));
}
