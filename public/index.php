<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../src/Core/CSPRNG.php';
require_once __DIR__ . '/../src/Core/Router.php';
require_once __DIR__ . '/../src/Localization/Translator.php';
require_once __DIR__ . '/../src/Generators/PasswordGenerator.php';
require_once __DIR__ . '/../src/Generators/PassphraseGenerator.php';
require_once __DIR__ . '/../src/Generators/UsernameGenerator.php';

use CoreCreds\Core\Router;
use CoreCreds\Localization\Translator;
use CoreCreds\Generators\PasswordGenerator;
use CoreCreds\Generators\PassphraseGenerator;
use CoreCreds\Generators\UsernameGenerator;

$translator = new Translator();
$router = new Router();

$router->add('POST', '/api/generate', function() {
    header('Content-Type: application/json');
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $type = $input['type'] ?? 'password';

    if ($type === 'password') {
        echo json_encode(PasswordGenerator::generate($input));
    } elseif ($type === 'passphrase') {
        echo json_encode(PassphraseGenerator::generate($input));
    } elseif ($type === 'username') {
        echo json_encode(UsernameGenerator::generate($input));
    } else {
        echo json_encode(['error' => 'Invalid type']);
    }
});

$router->add('GET', '', function() use ($translator) {
    $lang = $translator->getAll();
    ?>
    <!DOCTYPE html>
    <html lang="<?php echo $translator->getLanguage(); ?>" class="h-full">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $lang['title']; ?></title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="h-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200" x-data="appData">
        
        <div class="max-w-4xl mx-auto px-4 py-8" x-init="initTimer()">
            <header class="flex justify-between items-center mb-8 border-b border-slate-200 dark:border-slate-700 pb-4">
                <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">CoreCreds</h1>
                <div class="flex items-center space-x-4">
                    <button @click="showDonate = true" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-lg shadow font-medium transition"><?php echo $lang['donate']; ?></button>
                    <a href="https://github.com/rondevhub/corecreds" target="_blank" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-lg shadow font-medium transition"><?php echo $lang['repo']; ?></a>
                </div>
            </header>

            <main class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-1 flex space-x-1">
                        <button @click="tab = 'password'" :class="tab === 'password' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-3 text-center rounded-xl font-semibold transition"><?php echo $lang['tab_passwords']; ?></button>
                        <button @click="tab = 'passphrase'" :class="tab === 'passphrase' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-3 text-center rounded-xl font-semibold transition"><?php echo $lang['tab_passphrases']; ?></button>
                        <button @click="tab = 'username'" :class="tab === 'username' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-3 text-center rounded-xl font-semibold transition"><?php echo $lang['tab_usernames']; ?></button>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 min-h-[100px] flex flex-col justify-center items-center relative group border border-slate-100 dark:border-slate-700">
                        <div x-text="output || '...'" class="text-2xl font-mono tracking-wider text-center select-all break-all pr-12 font-bold text-slate-800 dark:text-white"></div>
                        <button x-show="output" @click="copyToClipboard()" class="absolute right-4 p-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-xl transition shadow-sm">
                            <svg x-show="!copied" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                            <span x-show="copied" class="text-sm font-bold text-green-600 dark:text-green-400"><?php echo $lang['copied']; ?></span>
                        </button>
                    </div>

                    <div x-show="tab === 'password' || tab === 'passphrase'" class="w-full h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden transition-all duration-300">
                        <div :class="strengthClasses[strength]" class="h-full transition-all duration-500" :style="`width: ${(strength + 1) * 20}%`"></div>
                    </div>
                    <div x-show="tab === 'password' || tab === 'passphrase'" class="text-right text-sm font-semibold -mt-4 text-slate-500">
                        <span x-text="strengthText[strength]"></span>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-100 dark:border-slate-700">
                        <div x-show="tab === 'password'" class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['len']; ?>: <span x-text="passwordOpts.length" class="text-blue-600"></span></label>
                                <input type="range" min="8" max="64" x-model="passwordOpts.length" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passwordOpts.uppercase" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['uppercase']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passwordOpts.lowercase" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['lowercase']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passwordOpts.numbers" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['numbers']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passwordOpts.symbols" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['symbols']; ?></span>
                                </label>
                            </div>
                            <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                <input type="checkbox" x-model="passwordOpts.exclude_similar" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                <span class="font-medium"><?php echo $lang['exclude_similar']; ?></span>
                            </label>
                        </div>

                        <div x-show="tab === 'passphrase'" class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['word_count']; ?>: <span x-text="passphraseOpts.word_count" class="text-blue-600"></span></label>
                                <input type="range" min="3" max="12" x-model="passphraseOpts.word_count" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['wordlist']; ?></label>
                                <select x-model="passphraseOpts.wordlist" class="w-full p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 focus:outline-none focus:border-blue-600 transition">
                                    <option value="mix"><?php echo $lang['wordlist_mix']; ?></option>
                                    <option value="dice-de">dice-de.txt</option>
                                    <option value="dice-lat">dice-lat.txt</option>
                                    <option value="eff">eff.txt</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passphraseOpts.word_start_upper" @change="if(passphraseOpts.word_start_upper) passphraseOpts.word_start_mix=false" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['word_start_upper']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passphraseOpts.word_start_mix" @change="if(passphraseOpts.word_start_mix) passphraseOpts.word_start_upper=false" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['word_start_mix']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passphraseOpts.numbers" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['numbers']; ?></span>
                                </label>
                                <label class="flex items-center p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl cursor-pointer select-none border border-transparent has-[:checked]:border-blue-600 transition">
                                    <input type="checkbox" x-model="passphraseOpts.symbols" class="w-5 h-5 text-blue-600 rounded accent-blue-600 mr-3">
                                    <span class="font-medium"><?php echo $lang['symbols']; ?></span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['separator']; ?></label>
                                <select x-model="passphraseOpts.separator" class="w-full p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 focus:outline-none focus:border-blue-600 transition">
                                    <option value=" ">Leerschritt ( )</option>
                                    <option value="-">Bindestrich (-)</option>
                                    <option value="_">Unterstrich (_)</option>
                                    <option value=".">Punkt (.)</option>
                                    <option value="none">Keines</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="tab === 'username'" class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['base_name']; ?></label>
                                <input type="text" x-model="usernameOpts.base_name" class="w-full p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 focus:outline-none focus:border-blue-600 transition" placeholder="z.B. Rocky">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['digit_count']; ?>: <span x-text="usernameOpts.digit_count" class="text-blue-600"></span></label>
                                <input type="range" min="0" max="8" x-model="usernameOpts.digit_count" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-2"><?php echo $lang['placement']; ?></label>
                                <select x-model="usernameOpts.placement" class="w-full p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 focus:outline-none focus:border-blue-600 transition">
                                    <option value="end"><?php echo $lang['place_end']; ?></option>
                                    <option value="start"><?php echo $lang['place_start']; ?></option>
                                    <option value="random"><?php echo $lang['place_random']; ?></option>
                                </select>
                            </div>
                        </div>

                        <button @click="generate()" class="w-full mt-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 active:scale-[0.98] transition">
                            <?php echo $lang['generate']; ?>
                        </button>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-3 text-blue-600 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <?php echo $lang['desc_title']; ?>
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo $lang['desc_text']; ?></p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-6 border border-slate-100 dark:border-slate-700">
                        <h3 class="text-lg font-bold mb-3 text-indigo-600 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                            <?php echo $lang['sec_title']; ?>
                        </h3>
                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo $lang['sec_text']; ?></p>
                    </div>
                </div>
            </main>
        </div>

        <div x-show="showDonate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition>
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl max-w-md w-full p-6 border border-slate-100 dark:border-slate-700" @click.away="showDonate = false">
                <h3 class="text-xl font-bold mb-3"><?php echo $lang['donate_modal_title']; ?></h3>
                <p class="text-slate-600 dark:text-slate-400 text-sm mb-6 leading-relaxed"><?php echo $lang['donate_modal_text']; ?></p>
                <div class="flex space-x-3">
                    <button @click="showDonate = false" class="flex-1 py-3 bg-slate-100 dark:bg-slate-700 font-semibold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition"><?php echo $lang['close']; ?></button>
                </div>
            </div>
        </div>

        <div x-show="timeoutWarning" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-red-600 text-white font-bold px-6 py-3 rounded-xl shadow-2xl transition" x-transition>
            Inaktivität erkannt! Cache & Speicher werden geleert...
        </div>

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('appData', () => ({
                    tab: 'password',
                    output: '',
                    strength: 0,
                    copied: false,
                    showDonate: false,
                    timeoutWarning: false,
                    timer: null,
                    passwordOpts: { length: 16, uppercase: true, lowercase: true, numbers: true, symbols: true, exclude_similar: false },
                    passphraseOpts: { word_count: 5, wordlist: 'mix', word_start_upper: true, word_start_mix: false, numbers: false, symbols: false, separator: ' ' },
                    usernameOpts: { base_name: '', digit_count: 3, placement: 'end' },
                    strengthClasses: {
                        0: 'bg-red-600',
                        1: 'bg-orange-500',
                        2: 'bg-yellow-500',
                        3: 'bg-blue-600',
                        4: 'bg-green-600'
                    },
                    strengthText: {
                        0: '<?php echo $lang["strength_0"]; ?>',
                        1: '<?php echo $lang["strength_1"]; ?>',
                        2: '<?php echo $lang["strength_2"]; ?>',
                        3: '<?php echo $lang["strength_3"]; ?>',
                        4: '<?php echo $lang["strength_4"]; ?>'
                    },
                    initTimer() {
                        const reset = () => {
                            clearTimeout(this.timer);
                            this.timer = setTimeout(() => {
                                this.timeoutWarning = true;
                                setTimeout(() => {
                                    window.location.reload(true);
                                }, 1500);
                            }, 120000);
                        };
                        window.addEventListener('mousemove', reset);
                        window.addEventListener('keydown', reset);
                        window.addEventListener('click', reset);
                        reset();
                        this.generate();
                    },
                    async generate() {
                        let payload = { type: this.tab };
                        if (this.tab === 'password') payload = { ...payload, ...this.passwordOpts };
                        if (this.tab === 'passphrase') payload = { ...payload, ...this.passphraseOpts };
                        if (this.tab === 'username') payload = { ...payload, ...this.usernameOpts };

                        try {
                            const res = await fetch('/api/generate', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify(payload)
                            });
                            const data = await res.json();
                            this.output = data.result;
                            this.strength = data.strength ?? 0;
                        } catch (e) {
                            this.output = 'Error connecting to server.';
                        }
                    },
                    copyToClipboard() {
                        if (!this.output) return;
                        navigator.clipboard.writeText(this.output);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                }));
            });
        </script>
    </body>
    </html>
    <?php
});

$router->handle();