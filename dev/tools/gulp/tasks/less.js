import gulp from 'gulp';
import ThemeRegistry from '../utils/theme-registry';
import less from 'gulp-less';
import sourceMaps from 'gulp-sourcemaps';
import util from 'gulp-util';
import { sync } from '../utils/sync';

export default function (done, theme) {
    const themeRegistry = new ThemeRegistry();
    const themeConfig = themeRegistry.getTheme(theme);
    if (!themeConfig) {
        throw new Error('Please specify theme after colon.');
    }

    return sync(gulp.src(themeConfig.preprocessorFiles)
        .pipe(sourceMaps.init())
        .pipe(less().on('error', util.log))
        .pipe(sourceMaps.write('.'))
        .pipe(gulp.dest(themeConfig.path + 'css/')));
}
