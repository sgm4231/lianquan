import Button from './src/Button'; 
import Loading from './src/Loading';
import OffsetPaginator from './src/OffsetPaginator';
import ProcessClickButton from './src/ProcessClickButton';

export default {install (Vue) {
  Vue.component('sb-'+Button.name, Button);
  Vue.component('sb-'+Loading.name, Loading);
  Vue.component('sb-'+OffsetPaginator.name, OffsetPaginator);
  Vue.component('sb-'+ProcessClickButton.name, ProcessClickButton);
}};
