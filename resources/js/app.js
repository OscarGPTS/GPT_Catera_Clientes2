import './bootstrap';

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm.js';
import 'flowbite';

// Register custom Alpine plugins/directives here (before Livewire.start())

window.Alpine = Alpine;
Livewire.start();
