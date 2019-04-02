import cleanTask from './dev/tools/gulp/tasks/clean';
import deployTask from './dev/tools/gulp/tasks/deploy';
import execTask from './dev/tools/gulp/tasks/exec';
import lessTask from './dev/tools/gulp/tasks/less';
import scssTask from './dev/tools/gulp/tasks/scss';
import watchTask from './dev/tools/gulp/tasks/watch';
import taskRegister from './dev/tools/gulp/utils/task-register';

taskRegister('clean', cleanTask);
taskRegister('deploy', deployTask);
taskRegister('exec', execTask, ['clean']);
taskRegister('less', lessTask);
taskRegister('scss', scssTask);
taskRegister('watch', watchTask);

taskRegister('build:scss', null, ['exec', 'scss']);
taskRegister('build:less', null, ['exec', 'less']);

taskRegister('dev:scss', null, ['exec', 'scss', 'watch']);
taskRegister('dev:less', null, ['exec', 'less', 'watch']);
