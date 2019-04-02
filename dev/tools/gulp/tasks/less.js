import gulp from 'gulp';
import ThemeRegistry from '../utils/theme-registry';
import less from 'gulp-less';
import sourceMaps from 'gulp-sourcemaps';
import log from 'fancy-log';
import { sync } from '../utils/sync';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);

    return sync(gulp.src(themeConfig.preprocessorFiles)
        .pipe(sourceMaps.init())
        .pipe(less().on('error', log.error))
        .pipe(sourceMaps.write('.'))
        .pipe(gulp.dest(themeConfig.path + 'css/')));
}
