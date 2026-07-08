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

$router->add('POST', '/api/generate', function () {
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

$router->add('GET', '', function () use ($translator) {
    $lang = $translator->getAll();
?>
    <!DOCTYPE html>
    <html lang="<?php echo $translator->getLanguage(); ?>" class="h-full">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="CoreCreds: Sicherer & lokaler Passwort-, Passphrase- und Benutzernamen-Generator. 100% Open Source, ohne Tracking und ohne Speicherung.">
        <meta name="keywords" content="sicheres passwort generieren, passphrase generator deutsch, online passwort generator, username generator, diceware generator, corecreds, rondevhub">
        <meta name="author" content="Ronny Melzer (rondevhub)">
        <link rel="icon" type="image/png" href="assets/icon/CoreCreds.png">
        <title><?php echo $lang['title']; ?></title>
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <style>
            .no-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .no-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
    </head>

    <body class="h-full bg-slate-50 dark:bg-slate-900 text-slate-800 dark:text-slate-100 transition-colors duration-200" x-data="appData">

        <div class="max-w-4xl mx-auto px-4 py-4 sm:py-8" x-init="initTimer()">
            <header class="flex flex-col sm:flex-row justify-between items-center mb-6 sm:mb-8 border-b border-slate-200 dark:border-slate-700 pb-4 space-y-4 sm:space-y-0">
                <div class="flex items-center space-x-3">
                    <img src="assets/icon/CoreCreds.png" alt="CoreCreds Logo" class="h-12 sm:h-16 w-auto object-contain">
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">
                        CoreCreds
                    </h1>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4 w-full sm:w-auto justify-center sm:justify-end">
                    <button @click="showDonate = true" class="flex-1 sm:flex-none flex justify-center items-center p-2 sm:px-4 sm:py-2 bg-lime-300 hover:bg-lime-200 text-white rounded-lg shadow text-xs sm:text-sm font-medium transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 512 512">
                            <path fill="#ff4f81" d="M378.9 80c-27.3 0-53 13.1-69 35.2l-34.4 47.6c-4.5 6.2-11.7 9.9-19.4 9.9s-14.9-3.7-19.4-9.9l-34.4-47.6c-16-22.1-41.7-35.2-69-35.2-47 0-85.1 38.1-85.1 85.1 0 49.9 32 98.4 68.1 142.3 41.1 50 91.4 94 125.9 120.3 3.2 2.4 7.9 4.2 14 4.2s10.8-1.8 14-4.2c34.5-26.3 84.8-70.4 125.9-120.3 36.2-43.9 68.1-92.4 68.1-142.3 0-47-38.1-85.1-85.1-85.1zM271 87.1c25-34.6 65.2-55.1 107.9-55.1 73.5 0 133.1 59.6 133.1 133.1 0 68.6-42.9 128.9-79.1 172.8-44.1 53.6-97.3 100.1-133.8 127.9-12.3 9.4-27.5 14.1-43.1 14.1s-30.8-4.7-43.1-14.1C176.4 438 123.2 391.5 79.1 338 42.9 294.1 0 233.7 0 165.1 0 91.6 59.6 32 133.1 32 175.8 32 216 52.5 241 87.1l15 20.7 15-20.7z" />
                        </svg>
                    </button>
                    <a href="https://github.com/rondevhub/corecreds" target="_blank" class="flex-1 sm:flex-none flex justify-center items-center p-2 sm:px-4 sm:py-2 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 rounded-lg shadow transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-slate-800 dark:text-slate-100" viewBox="0 0 512 512">
                            <path fill="currentColor" d="M216.5 362.5c-66-8-112.5-55.5-112.5-117 0-25 9-52 24-70-6.5-16.5-5.5-51.5 2-66 20-2.5 47 8 63 22.5 19-6 39-9 63.5-9s44.5 3 62.5 8.5c15.5-14 43-24.5 63-22 7 13.5 8 48.5 1.5 65.5 16 19 24.5 44.5 24.5 70.5 0 61.5-46.5 108-113.5 116.5 17 11 28.5 35 28.5 62.5l0 52C323 491.5 335.5 500 350.5 494 441 459.5 512 369 512 257 512 115.5 397 0 255.5 0S0 115.5 0 257c0 111 70.5 203 165.5 237.5 13.5 5 26.5-4 26.5-17.5l0-40c-7 3-16 5-24 5-33 0-52.5-18-66.5-51.5-5.5-13.5-11.5-21.5-23-23-6-.5-8-3-8-6 0-6 10-10.5 20-10.5 14.5 0 27 9 40 27.5 10 14.5 20.5 21 33 21s20.5-4.5 32-16c8.5-8.5 15-16 21-21z" />
                        </svg>
                    </a>
                </div>
            </header>

            <main class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
                <div class="md:col-span-2 space-y-4 sm:space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-xl p-1 flex space-x-1 overflow-x-auto no-scrollbar">
                        <button @click="switchTab('password')" :class="tab === 'password' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-2 sm:py-3 text-center rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition whitespace-nowrap px-2"><?php echo $lang['tab_passwords']; ?></button>
                        <button @click="switchTab('passphrase')" :class="tab === 'passphrase' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-2 sm:py-3 text-center rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition whitespace-nowrap px-2"><?php echo $lang['tab_passphrases']; ?></button>
                        <button @click="switchTab('username')" :class="tab === 'username' ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700'" class="flex-1 py-2 sm:py-3 text-center rounded-lg sm:rounded-xl text-xs sm:text-sm md:text-base font-semibold transition whitespace-nowrap px-2"><?php echo $lang['tab_usernames']; ?></button>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 min-h-[80px] sm:min-h-[100px] flex flex-col justify-center items-center relative group border border-slate-100 dark:border-slate-700">
                        <div x-text="output || '...'" class="text-lg sm:text-2xl font-mono tracking-wider text-center select-all break-all pr-10 sm:pr-12 font-bold text-slate-800 dark:text-white w-full"></div>
                        <button x-show="output" @click="copyToClipboard()" class="absolute right-2 sm:right-4 p-1.5 sm:p-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg sm:rounded-xl transition shadow-sm">
                            <svg x-show="!copied" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                            </svg>
                            <span x-show="copied" class="text-xs sm:text-sm font-bold text-green-600 dark:text-green-400"><?php echo $lang['copied']; ?></span>
                        </button>
                    </div>

                    <div x-show="(tab === 'password' || tab === 'passphrase') && output" class="w-full h-2.5 sm:h-3 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden transition-all duration-300">
                        <div :class="strengthClasses[strength]" class="h-full transition-all duration-500" :style="`width: ${(strength + 1) * 20}%`"></div>
                    </div>
                    <div x-show="(tab === 'password' || tab === 'passphrase') && output" class="text-right text-xs sm:text-sm font-semibold -mt-3 sm:-mt-4 text-slate-500">
                        <span x-text="strengthText[strength]"></span>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-slate-100 dark:border-slate-700">
                        <div x-show="tab === 'password'" class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['len']; ?>: <span x-text="passwordOpts.length" class="text-blue-600"></span></label>
                                <input type="range" min="8" max="64" x-model="passwordOpts.length" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <button type="button" @click="passwordOpts.uppercase = !passwordOpts.uppercase" :class="passwordOpts.uppercase ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['uppercase']; ?></span>
                                    <span x-show="passwordOpts.uppercase" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passwordOpts.lowercase = !passwordOpts.lowercase" :class="passwordOpts.lowercase ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['lowercase']; ?></span>
                                    <span x-show="passwordOpts.lowercase" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passwordOpts.numbers = !passwordOpts.numbers" :class="passwordOpts.numbers ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['numbers']; ?></span>
                                    <span x-show="passwordOpts.numbers" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passwordOpts.symbols = !passwordOpts.symbols" :class="passwordOpts.symbols ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['symbols']; ?></span>
                                    <span x-show="passwordOpts.symbols" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                            </div>
                            <button type="button" @click="passwordOpts.exclude_similar = !passwordOpts.exclude_similar" :class="passwordOpts.exclude_similar ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="w-full flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                <span class="flex-1"><?php echo $lang['exclude_similar']; ?></span>
                                <span x-show="passwordOpts.exclude_similar" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                            </button>
                        </div>

                        <div x-show="tab === 'passphrase'" class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['word_count']; ?>: <span x-text="passphraseOpts.word_count" class="text-blue-600"></span></label>
                                <input type="range" min="3" max="12" x-model="passphraseOpts.word_count" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['wordlist']; ?></label>
                                <select x-model="passphraseOpts.wordlist" class="w-full p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 text-sm sm:text-base focus:outline-none focus:border-blue-600 transition">
                                    <option value="mix"><?php echo $lang['wordlist_mix']; ?></option>
                                    <option value="dice-de">dice-de.txt</option>
                                    <option value="dice-la">dice-la.txt</option>
                                    <option value="eff">eff.txt</option>
                                </select>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <button type="button" @click="passphraseOpts.word_start_upper = !passphraseOpts.word_start_upper; if(passphraseOpts.word_start_upper) passphraseOpts.word_start_mix=false" :class="passphraseOpts.word_start_upper ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['word_start_upper']; ?></span>
                                    <span x-show="passphraseOpts.word_start_upper" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passphraseOpts.word_start_mix = !passphraseOpts.word_start_mix; if(passphraseOpts.word_start_mix) passphraseOpts.word_start_upper=false" :class="passphraseOpts.word_start_mix ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['word_start_mix']; ?></span>
                                    <span x-show="passphraseOpts.word_start_mix" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passphraseOpts.numbers = !passphraseOpts.numbers" :class="passphraseOpts.numbers ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['numbers']; ?></span>
                                    <span x-show="passphraseOpts.numbers" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                                <button type="button" @click="passphraseOpts.symbols = !passphraseOpts.symbols" :class="passphraseOpts.symbols ? 'border-blue-600 bg-blue-50/20 dark:bg-blue-900/20 text-blue-600' : 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-700/50'" class="flex items-center p-3 sm:p-4 rounded-xl cursor-pointer select-none border text-left font-medium text-sm sm:text-base transition">
                                    <span class="flex-1"><?php echo $lang['symbols']; ?></span>
                                    <span x-show="passphraseOpts.symbols" class="text-blue-600 font-bold text-xs bg-blue-100 dark:bg-blue-900 px-2 py-0.5 rounded">AKTIV</span>
                                </button>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['separator']; ?></label>
                                <select x-model="passphraseOpts.separator" class="w-full p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 text-sm sm:text-base focus:outline-none focus:border-blue-600 transition">
                                    <option value="-">Bindestrich (-)</option>
                                    <option value=" ">Leerschritt ( )</option>
                                    <option value="_">Unterstrich (_)</option>
                                    <option value="@">Ät (@)</option>
                                    <option value=".">Punkt (.)</option>
                                    <option value="none">Keines</option>
                                </select>
                            </div>
                        </div>

                        <div x-show="tab === 'username'" class="space-y-4 sm:space-y-6">
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['base_name']; ?></label>
                                <input type="text" x-model="usernameOpts.base_name" class="w-full p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 text-sm sm:text-base focus:outline-none focus:border-blue-600 transition" placeholder="z.B. Rocky">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['username_case']; ?></label>
                                <select x-model="usernameOpts.username_case" class="w-full p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 text-sm sm:text-base focus:outline-none focus:border-blue-600 transition">
                                    <option value="default"><?php echo $lang['case_default']; ?></option>
                                    <option value="upper"><?php echo $lang['case_upper']; ?></option>
                                    <option value="lower"><?php echo $lang['case_lower']; ?></option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['digit_count']; ?>: <span x-text="usernameOpts.digit_count" class="text-blue-600"></span></label>
                                <input type="range" min="0" max="8" x-model="usernameOpts.digit_count" class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                            </div>
                            <div>
                                <label class="block text-xs sm:text-sm font-bold mb-2"><?php echo $lang['placement']; ?></label>
                                <select x-model="usernameOpts.placement" class="w-full p-2.5 sm:p-3 bg-slate-50 dark:bg-slate-700 rounded-xl border border-slate-200 dark:border-slate-600 text-sm sm:text-base focus:outline-none focus:border-blue-600 transition">
                                    <option value="end"><?php echo $lang['place_end']; ?></option>
                                    <option value="start"><?php echo $lang['place_start']; ?></option>
                                    <option value="random"><?php echo $lang['place_random']; ?></option>
                                </select>
                            </div>
                        </div>

                        <button @click="generate()" class="w-full mt-4 sm:mt-6 py-3.5 sm:py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/20 text-sm sm:text-base active:scale-[0.98] transition">
                            <?php echo $lang['generate']; ?>
                        </button>
                    </div>
                </div>

                <div class="space-y-4 sm:space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-slate-100 dark:border-slate-700">
                        <h3 class="text-base sm:text-lg font-bold mb-2 sm:mb-3 text-blue-600 flex items-center">
                            <svg class="w-10 h-10 mr-2" fill="none" stroke="currentColor" viewBox="0 0 512 512">
                                <path fill="currentColor" d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464a256 256 0 1 0 0-512 256 256 0 1 0 0 512zM216 336c-13.3 0-24 10.7-24 24s10.7 24 24 24l80 0c13.3 0 24-10.7 24-24s-10.7-24-24-24l-8 0 0-88c0-13.3-10.7-24-24-24l-48 0c-13.3 0-24 10.7-24 24s10.7 24 24 24l24 0 0 64-24 0zm40-144a32 32 0 1 0 0-64 32 32 0 1 0 0 64z" />
                            </svg>
                            <?php echo $lang['desc_title']; ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo $lang['desc_text']; ?></p>
                    </div>
                    <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-xl p-4 sm:p-6 border border-slate-100 dark:border-slate-700">
                        <h3 class="text-base sm:text-lg font-bold mb-2 sm:mb-3 text-indigo-600 flex items-center">
                            <svg class="w-10 h-10 mr-2" fill="none" stroke="currentColor" viewBox="0 0 512 512">
                                <path fill="currentColor" d="M256.1 0c4.6 0 9.2 1 13.3 2.9L457.8 82.8c22 9.3 38.4 31 38.3 57.2-.5 99.2-41.3 280.7-213.6 363.2-16.7 8-36.1 8-52.8 0-172.4-82.5-213.2-263.9-213.7-363.2-.1-26.2 16.3-47.9 38.3-57.2L242.7 2.9C246.8 1 251.4 0 256.1 0zM73.1 127c-5.9 2.5-9.1 7.7-9 12.7 .5 91.4 38.4 249.3 186.4 320.1 3.6 1.7 7.8 1.7 11.3 0 148-70.8 185.9-228.7 186.3-320.1 0-5-3.1-10.2-9-12.7l-183-77.6-183 77.6zm240.3 34.9c7.8-10.7 22.8-13.1 33.5-5.3 10.7 7.8 13.1 22.8 5.3 33.5L249.8 330.9c-4.2 5.7-10.7 9.3-17.8 9.8s-14-2.2-18.9-7.3l-46.4-48c-9.2-9.5-9-24.7 .6-33.9 9.5-9.2 24.7-8.9 33.9 .6l26.5 27.4 85.6-117.7z" />
                            </svg>
                            <?php echo $lang['sec_title']; ?>
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed"><?php echo $lang['sec_text']; ?></p>
                    </div>
                </div>
            </main>
            <footer class="relative z-10 w-full p-6 text-center text-xs opacity-60 flex flex-wrap justify-center gap-x-6 gap-y-3">
                <div class="w-full text-center mt-2">
                    <span class="tsm:text-sm text-slate-900 dark:text-slate-200">Erstellt mit ❤️ und ☕️ von <a href="https://rondev.de" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-slate-300 hover:underline font-bold">RonDev</a> - <a href="https://github.com/RonDevHub/CoreCreds" target="_blank" rel="noopener noreferrer" class="text-indigo-600 dark:text-slate-300 hover:underline font-bold">Github</a></span>
                </div>
            </footer>
        </div>

        <div x-show="showDonate" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" x-transition>
            <div class="bg-white dark:bg-slate-800 rounded-xl sm:rounded-2xl shadow-2xl max-w-md w-full p-4 sm:p-6 border border-slate-100 dark:border-slate-700" @click.away="showDonate = false">
                <h3 class="text-lg sm:text-xl font-bold mb-2 sm:mb-3"><?php echo $lang['donate_modal_title']; ?></h3>
                <p class="text-slate-600 dark:text-slate-400 text-xs sm:text-sm mb-4 sm:mb-6 leading-relaxed"><?php echo $lang['donate_modal_text']; ?></p>
                <p class="p-5 font-bold text-lg text-center text-amber-400"><a href="https://rondev.de/donate" target="_blank" rel="nofollow">Donate</a></p>
                <div class="flex space-x-3">
                    <button @click="showDonate = false" class="flex-1 py-2.5 sm:py-3 bg-slate-100 dark:bg-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 dark:hover:bg-slate-600 transition"><?php echo $lang['close']; ?></button>
                </div>
            </div>
        </div>

        <div x-show="timeoutWarning" class="fixed top-4 left-1/2 -translate-x-1/2 z-50 bg-red-600 text-white text-sm sm:text-base font-bold px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl shadow-2xl transition" x-transition>
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
                    passwordOpts: {
                        length: 16,
                        uppercase: true,
                        lowercase: true,
                        numbers: true,
                        symbols: true,
                        exclude_similar: false
                    },
                    passphraseOpts: {
                        word_count: 5,
                        wordlist: 'mix',
                        word_start_upper: true,
                        word_start_mix: false,
                        numbers: false,
                        symbols: false,
                        separator: ' '
                    },
                    usernameOpts: {
                        base_name: '',
                        username_case: 'default',
                        digit_count: 3,
                        placement: 'end'
                    },
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
                        // Beim Laden KEIN automatisches generate() aufrufen!
                    },
                    switchTab(newTab) {
                        this.tab = newTab;
                        this.output = ''; // Feld beim Umschalten leeren
                    },
                    async generate() {
                        let payload = {
                            type: this.tab
                        };
                        if (this.tab === 'password') payload = {
                            ...payload,
                            ...this.passwordOpts
                        };
                        if (this.tab === 'passphrase') payload = {
                            ...payload,
                            ...this.passphraseOpts
                        };
                        if (this.tab === 'username') payload = {
                            ...payload,
                            ...this.usernameOpts
                        };

                        try {
                            const res = await fetch('/api/generate', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(payload)
                            });
                            if (!res.ok) throw new Error();
                            const data = await res.json();
                            this.output = data.result;
                            this.strength = data.strength ?? 0;
                        } catch (e) {
                            this.output = 'Error connecting to server.';
                        }
                    },
                    copyToClipboard() {
                        if (!this.output) return;

                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(this.output).then(() => {
                                this.fireCopiedState();
                            }).catch(() => {
                                this.fallbackCopyToClipboard();
                            });
                        } else {
                            this.fallbackCopyToClipboard();
                        }
                    },
                    fallbackCopyToClipboard() {
                        try {
                            const textArea = document.createElement("textarea");
                            textArea.value = this.output;
                            textArea.style.top = "0";
                            textArea.style.left = "0";
                            textArea.style.position = "fixed";
                            textArea.style.opacity = "0";
                            document.body.appendChild(textArea);
                            textArea.focus();
                            textArea.select();
                            const successful = document.execCommand('copy');
                            document.body.removeChild(textArea);
                            if (successful) {
                                this.fireCopiedState();
                            }
                        } catch (err) {
                            console.error('Fallback copy failed', err);
                        }
                    },
                    fireCopiedState() {
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