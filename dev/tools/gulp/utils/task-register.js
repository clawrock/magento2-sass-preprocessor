import gulp from 'gulp';
import filesRouter from '../../grunt/tools/files-router';

export default function(task, callback, deps = []) {
    filesRouter.set('themes', './dev/tools/grunt/configs/themes');
    const themes = filesRouter.get('themes');
    Object.keys(themes).forEach(theme => {
        gulp.task(`${task}:${theme}`, deps.map(el => `${el}:${theme}`), (done) => {
            return callback(done, theme);
        });
    });
    gulp.task(task, deps, callback);
}
