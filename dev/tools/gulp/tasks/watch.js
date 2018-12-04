import gulp from 'gulp';
import ThemeRegistry from '../utils/theme-registry';
import config from '../config';
import { initSync } from '../utils/sync';
import path from 'path';
import gutil from 'gulp-util';
import chalk from 'chalk';

function runThemeTask(task, file) {
    const themeRegistry = new ThemeRegistry();
    const theme = themeRegistry.getThemeKeyByFile(file);
    if (!theme) {
        gutil.log(chalk.red(`Theme task not found for this file!`));
    }

    if (gulp.tasks[`${task}:${theme}`]) {
        gulp.start(`${task}:${theme}`);

        return;
    }
    gutil.log(chalk.red(`Task ${task}:${theme} not found!`));
}

function relativizePath(absolutePath) {
    return path.relative(process.cwd(), absolutePath);
}

export default function (done, theme) {
    const pubPath = `${config.projectPath}/pub/static`;

    initSync();

    if (theme) {
        const themeRegistry = new ThemeRegistry();
        const themeConfig = themeRegistry.getTheme(theme);
        gulp.watch(`${themeConfig.path}**/*.${themeConfig.dsl}`, [`${themeConfig.dsl}:${theme}`]).on('change', stream => {
            gutil.log(chalk.white(`File ${relativizePath(stream.path)} was changed`));
        });

        return;
    }

    gulp.watch(`${pubPath}/**/*.less`).on('change', stream => {
        gutil.log(chalk.white(`File ${relativizePath(stream.path)} was changed`));

        runThemeTask(`less`, stream.path);

    });

    gulp.watch(`${pubPath}/**/*.scss`).on('change', stream => {
        gutil.log(chalk.white(`File ${relativizePath(stream.path)} was changed`));

        runThemeTask(`scss`, stream.path);
    });
}
