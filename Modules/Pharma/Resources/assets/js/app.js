import { InitApp } from '@/helpers/main'

import PharmaEarningFormOffcanvas from './components/PharmaEarningFormOffcanvas.vue'
import PharmaViewCommissions from './components/PharmaViewCommissions.vue'

const app = InitApp()

app.component('earning-pharma-form-offcanvas', PharmaEarningFormOffcanvas)
app.component('view-pharma-commissions-offcanvas', PharmaViewCommissions)

app.mount('[data-render="app"]');
