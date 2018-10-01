import gulp from 'gulp';
import ThemeRegistry from '../utils/theme-registry';
import config from '../config';

function runThemeTask(task, file) {
    const themeRegistry = new ThemeRegistry();
    const theme = themeRegistry.getThemeKeyByFile(file);
    if (!theme) {
        console.error(`Theme task not found for this file!`);
    }

    if (gulp.tasks[`${task}:${theme}`]) {
        gulp.start(`${task}:${theme}`);

        return;
    }
    console.error(`Task ${task}:${theme} not found!`);
}

export default function (done, theme) {
    const path = `${config.projectPath}/pub/static`;

    if (theme) {
        const themeRegistry = new ThemeRegistry();
        const themeConfig = themeRegistry.getTheme(theme);
        gulp.watch(`${themeConfig.path}**/*.${themeConfig.dsl}`, [`${themeConfig.dsl}:${theme}`]).on('change', stream => {
            console.log(`File ${stream.path} was changed`);
        });

        return;
    }

    gulp.watch(`${path}/**/*.less`).on('change', stream => {
        console.log(`File ${stream.path} was changed`);

        runThemeTask(`less`, stream.path);

    });

    gulp.watch(`${path}/**/*.scss`).on('change', stream => {
        console.log(`File ${stream.path} was changed`);

        runThemeTask(`scss`, stream.path);
    });
}
