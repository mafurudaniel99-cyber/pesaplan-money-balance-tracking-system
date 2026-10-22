<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;

/**
 * Financial Education Center — "Jifunze Kusimamia Fedha".
 * Lessons are defined in code (no DB needed) so they remain easy to maintain.
 */
final class EducationController extends Controller
{
    public function index(): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $this->view('education/index', [
            'activePage' => 'education',
            'lessons' => self::lessons(),
        ]);
    }

    public function lesson(string $slug): void
    {
        $this->auth->requireAuth();
        $this->auth->requireOnboarding();
        $lesson = null;
        foreach (self::lessons() as $l) {
            if ($l['slug'] === $slug) { $lesson = $l; break; }
        }
        if (!$lesson) {
            $this->redirect('/education', 'Somo halipatikani.', 'error');
        }
        $this->view('education/lesson', [
            'activePage' => 'education',
            'lesson' => $lesson,
            'lessons' => self::lessons(),
        ]);
    }

    /**
     * Lesson catalogue — Kiswahili content. Each lesson includes:
     *   - explanation (long-form Kiswahili text)
     *   - example (practical TZS example)
     *   - mistake (common mistake)
     *   - action (recommended action)
     *   - exercise (short interactive exercise)
     */
    public static function lessons(): array
    {
        return [
            [
                'slug' => 'nidhamu-ya-fedha',
                'title' => 'Nidhamu ya fedha ni nini?',
                'icon' => 'fa-compass',
                'summary' => 'Nidhamu ya fedha ni uwezo wa kufuata mpango wako badala ya kutumia kwa hisia.',
                'explanation' => 'Nidhamu ya fedha haimaanishi kuwa na pesa nyingi. Inamaanisha kufuatilia kila shilingi inayoingia na kutoka, kufanya maamuzi mazuri ya matumizi, na kujenga tabia ya kuweka akiba kwa kila kipato. Bila nidhamu, hata mshahara mkubwa unaweza kutowatoshelezi. Nidhamu hujengwa kupitia mazoea madogo ya kila siku: kuandika miamala, kufuata bajeti, na kufikiria kabla ya kutumia.',
                'example' => 'Mfano: Una mshahara wa TZS 800,000. Bila nidhamu, unaweza kutumia TZS 50,000 kwa stakehehe kila wiki bila kujali, ikawa TZS 200,000 kwa mwezi. Hapo ndipo pesa inapokwenda bila kujulikana. Na nidhamu, unaweza kupanga TZS 50,000 kwa starehe, TZS 100,000 kwa akiba, na TZS 650,000 kwa mahitaji muhimu.',
                'mistake' => 'Kosa la kawaida: kufikiri "nitaweka akiba baada ya kutumia". Pesa inayobaki mara nyingi ni sifuri.',
                'action' => 'Anza leo: weka akiba yako KABLA ya matumizi mengine yoyote, hata kama ni TZS 5,000 tu.',
                'exercise' => 'Andika kwenye daftari: kwa kila shilingi 100 unayopata, unataka kuiweka wapi? Akiba? Matumizi? Linganisha.',
            ],
            [
                'slug' => 'mapato-matumizi-akiba',
                'title' => 'Tofauti kati ya mapato, matumizi, akiba, na fedha zinazopatikana',
                'icon' => 'fa-scale-balanced',
                'summary' => 'Pesa inayoingiza siyo sawa na pesa inayopatikana kwa matumizi.',
                'explanation' => 'Mapato ni pesa yoyote inayoingia (mshahara, biashara, zawadi). Matumizi ni pesa inayotoka kwa makusudi (chakula, kodi). Akiba ni sehemu ya mapato ambayo huhifadhiwa kwa siku zijazo. Fedha zinazopatikana ni tofauti — ni kiasi cha pesa ulichonayo sasa hivi kwenye akaunti au mkononi. Mtu anaweza kuwa na mapato mazuri lakini hakuna fedha zinazopatikana kwa sababu yote imetumika au imeshughulikiwa na deni.',
                'example' => 'Mfano: Mshahara TZS 1,000,000 ulipokelewa tarehe 28. Sasa ni tarehe 15. Umetumia TZS 800,000, na TZS 100,000 ni deni la bili. Fedha zinazopatikana = TZS 100,000 tu, sio TZS 1,000,000.',
                'mistake' => 'Kosa: kufikiri "Nina mshahara wa milioni moja, ndiyo nina uwezo wa kununua simu ya TZS 700,000". Hapana — unayo TZS 100,000 tu.',
                'action' => 'Tazama kila siku: "Fedha zipi zinapatikana sasa?" sio "Mshahara wako ni kiasi gani?"',
                'exercise' => 'Hesabu leo: mapato yote ya mwezi, jumla ya matumizi, na kiasi cha fedha ulichonazo sasa. Tofauti ni akiba au hasara.',
            ],
            [
                'slug' => 'tengeneza-bajeti',
                'title' => 'Jinsi ya kuandaa bajeti ya mwezi',
                'icon' => 'fa-table-list',
                'summary' => 'Bajeti ni mpango wa jinsi pesa itakavyotumika KABLA ya kutumika.',
                'explanation' => 'Bajeti si kizuizi — ni ramani. Inakupa uwezo wa kuamua mapema ni pesa ngapi itaenda kwenye kila kategoria. Bila bajeti, unatumia kwa hisia na kushtuka mwisho wa mwezi. Bajeti nzuri inaanza na mapato halisi, kisha inaweka kiasi mahususi kwa: mahitaji muhimu (chakula, kodi, usafiri), majukumu (madeni, bili), akiba, na mwisho starehe. Bajeti inaweza kubadilishwa wakati wa mwezi lakini kila mabadiliko yanapaswa kufikiriwa.',
                'example' => 'Mfano: Mshahara TZS 900,000. Bajeti: Chakula TZS 200,000, Kodi TZS 150,000, Usafiri TZS 80,000, Bili TZS 70,000, Akiba TZS 100,000, Madeni TZS 50,000, Starehe TZS 100,000, Dharura TZS 50,000, Zilizopo TZS 100,000.',
                'mistake' => 'Kosa: kuweka bajeti bila kuzingatia mapato halisi. Haliwezekani kuwekeza TZS 1,200,000 wakati mapato ni TZS 900,000.',
                'action' => 'Tengeneza bajeti leo kwa mwezi ujao. Anza na kategoria muhimu kwanza.',
                'exercise' => 'Andika mapato yako halisi. Gawanya kwenye kategoria 5 muhimu zaidi. Angalia kama jumla inalingana na mapato.',
            ],
            [
                'slug' => 'rekodi-miamala',
                'title' => 'Kwa nini kila muamala unapaswa kuandikwa',
                'icon' => 'fa-pen-to-square',
                'summary' => 'Bila kumbukumbu, huwezi kujua pesa yako inakwenda wapi.',
                'explanation' => 'Binadamu huwa na kumbukumbu ndogo. Baada ya wiki mbili, hutaweza kumbuka kwa nini ulitumia TZS 15,000 jana. Rekodi za miamala zinakupa picha kamili: ni siku gani unatumia zaidi, ni kategoria ipi inakula pesa nyingi, na ni wapi unaweza kupunguza. Hata kama unatumia TZS 500 tu kwa chai, liandike. Kila shilingi inahesabika. Rekodi pia hukusaidia kujua kama unafuata bajeti na kufuatilia akiba yako.',
                'example' => 'Mfano: Baada ya wiki moja ya kurekodi, unaona umetumia TZS 35,000 kwa "biskuti na soda" — jambo ambalo hujalifahamu. Hapo unaweza kubadilisha.',
                'mistake' => 'Kosa: kufikiri "Nitakumbuka". Baada ya siku chache, huwezi kumbuka kwa usahihi.',
                'action' => 'Weka kumbukumbu ya kila muamala ndani ya saa 24. Tumia simu yako kurekodi mara moja baada ya kulipia.',
                'exercise' => 'Andika miamala yote ya leo hata madogo. Mwisho wa wiki, tazama na utambue mifumo.',
            ],
            [
                'slug' => 'kuwa-na-pesi-vs-wezo',
                'title' => 'Kuwa na pesa dhidi ya uwezo wa kununua',
                'icon' => 'fa-circle-question',
                'summary' => 'Kuwa na pesa sio haki ya kuitumia bila kufikiri.',
                'explanation' => 'Watu wengi wanaangukia kwenye kosa la kufikiri "Nina pesa, basi ninaweza kununua". Lakini kuwa na pesa sio sawa na kuwa na uwezo wa kulinunua jambo bila kuathiri mahitaji mengine. Kabla ya kununua, jiulize: "Je, hii ni hitaji au tamani? Je, nina bajeti? Je, kuna jambo muhimu zaidi la kufanya na pesa hii?" Uwezo wa kununua unamaanisha kuwa pesa inayobaki baada ya ununuzi bado inatosha kwa mahitaji yako muhimu na majukumu yote.',
                'example' => 'Mfano: Una TZS 300,000 kwenye akaunti. Unataka kununua viatu cha TZS 250,000. Lakini bili ya umeme ya TZS 80,000 haijalipwa. Kwa kweli, huwezi kumudu viatu — fedha zako zinapaswa kwanza kwa bili.',
                'mistake' => 'Kosa: kutumia "nina pesa" kama sababu ya kununua, bila kuzingatia majukumu yajayo.',
                'action' => 'Kabla ya ununuzi wowote wa kipindi cha starehe, tumia mfumo wa "Kagua Kabla ya Kutumia".',
                'exercise' => 'Fikiria ununuzi wa hivi karibuni. Je, ulikuwa na uwezo wa kumudu? Je, uliathiri mahitaji mengine?',
            ],
            [
                'slug' => 'gawa-pes-kusudi',
                'title' => 'Jinsi ya kutenganisha pesa kulingana na kusudi',
                'icon' => 'fa-folder-tree',
                'summary' => 'Kila kundi la pesa linapaswa kuwa na kazi mahususi.',
                'explanation' => 'Pesa inapochanganywa pamoja bila mpango, inatumika kwa dharura na starehe, na shughuli muhimu zinabaki bila malipo. Tatizo: mshahara wote ukiwa kwenye akaunti moja, huwezi kujua ni kiasi gani ni kwa chakula, kodi, au akiba. Suluhisho: gawa pesa kwa kusudi. Hii inaweza kufanyika kimantiki (kwenye mfumo wa PesaPlan) au kimetali (kwenye akaunti tofauti). Muhimu ni kila shilingi kuwa na kazi. "Kila shilingi iwe na kusudi" — kanuni kuu ya PesaPlan.',
                'example' => 'Mfano: Mshahara TZS 1,000,000. Gawa: TZS 600,000 kwa mahitaji muhimu, TZS 150,000 kwa majukumu, TZS 150,000 kwa akiba, TZS 100,000 kwa starehe.',
                'mistake' => 'Kosa: kuweka pesa zote kwenye akaunti moja na kutumia bila mpango.',
                'action' => 'Tumia kipengele cha "Ugawaji wa fedha" kwenye PesaPlan kugawa mshahara wako kwa kusudi.',
                'exercise' => 'Orodhesha kusudi 5 kuu ambako pesa yako inapaswa kwenda. Kwa kila moja, weka asilimia unayotaka kuiweka.',
            ],
            [
                'slug' => 'muhimu-vs-starehe',
                'title' => 'Jinsi ya kutambua matumizi muhimu dhidi ya starehe',
                'icon' => 'fa-list-check',
                'summary' => 'Matumizi muhimu hayawezi kuepukwa; starehe yanaweza kusubiri.',
                'explanation' => 'Matumizi muhimu ni yale ambayo bila yako, maisha yako husimama: chakula, kodi, usafiri wa kazi, dawa. Matumizi ya starehe ni yale yanayoongeza raha lakini hayazuizi maisha: manunuzi ya siha, karamu, burudani. Kosa la kawaida ni kufanya starehe kuwa muhimu. Kwa mfano, simu mpya si muhimu wakati simu ya zamani bado inafanya kazi. Akiba ya dharura ni muhimu — bila yake, dharura ndogo inakuwa deni kubwa.',
                'example' => 'Mfano: Kiatu kipya cha TZS 150,000 — je, ni muhimu au starehe? Kama viatu vya zamani vimechanika kabisa na unahitaji kiatu cha kazi, ni muhimu. Kama unahitaji tu kwa sababu ya mtindo, ni starehe.',
                'mistake' => 'Kosa: kujiimarisha "nilihitaji" kwa kila ununuzi wa starehe.',
                'action' => 'Kabla ya ununuzi, uliza: "Je, bila hii maisha yangu yasimama? Je, nina kitu kinachofanya kazi kama hiki?"',
                'exercise' => 'Orodhesha matumizi 10 ya mwisho. Weka alama ya "M" (muhimu) au "S" (starehe) kwa kila moja. Jiuliza kwa uaminifu.',
            ],
            [
                'slug' => 'tofauti-za-bajeti',
                'title' => 'Jinsi ya kuhesabu tofauti za bajeti',
                'icon' => 'fa-calculator',
                'summary' => 'Tofauti ya bajeti inaonyesha ulipopita au kupungua',
                'explanation' => 'Tofauti ya bajeti (variance) = matumizi halisi − bajeti iliyopangwa. Kama tofauti ni chanya, umetumia zaidi (mbaya). Kama hasi, umetumia kidogo (zuri). Tofauti zinakusaidia kujifunza: kama kategoria ya "chakula" inazidi kila mwezi, labda bajeti yako ni ndogo mno au unahitaji kubadilisha tabia. Tofauti ndogo zinakubalika, lakini zinapaswa kuchambuawa kila mwezi ili kujifunza.',
                'example' => 'Mfano: Bajeti ya chakula = TZS 200,000. Halisi = TZS 250,000. Tofauti = +50,000 (umepita). Sababu: 1) bei iliongezeka, 2) ulinunua zaidi ya mpango, 3) wageni walikuja. Tambua sababu, kisha rekebisha.',
                'mistake' => 'Kosa: kupuuza tofauti badala ya kuzichambua.',
                'action' => 'Mwisho wa kila mwezi, angalia tofauti kwa kila kategoria na tambua sababu.',
                'exercise' => 'Tumia ripoti ya "Bajeti vs Halisi" kwenye PesaPlan. Andika sababu 1 kwa kategoria yenye tofauti kubwa.',
            ],
            [
                'slug' => 'akiba-ya-dharura',
                'title' => 'Jinsi ya kujenga akiba ya dharura',
                'icon' => 'fa-shield-halved',
                'summary' => 'Akiba ya dharura ni ulinzi wa kwanza dhidi ya deni',
                'explanation' => 'Akiba ya dharura ni pesa iliyohifadhiwa mahali penye urahisi wa kuipata, kwa matumizi ya dharura tu: ugonjwa, kazi kupotea, gari kuharibika. Bila akiba ya dharura, kila dharura inakuwa deni. Lengo la kwanza: wahi kuwa na akiba ya mwezi 1 wa mahitaji muhimu. Kisha lengo la mwezi 3, hadi mwezi 6. Akiba ya dharura haipaswi kuguswa kwa matumizi ya kawaida — ni "bima" yako binafsi.',
                'example' => 'Mfano: Mahitaji muhumu ya mwezi = TZS 500,000. Lengo la kwanza = TZS 500,000 (mwezi 1). Lengo la pili = TZS 1,500,000 (miezi 3). Lengo la mwisho = TZS 3,000,000 (miezi 6). Anza na TZS 10,000 kila wiki — itachukua muda lakini utaanza.',
                'mistake' => 'Kosa: kufikiri "Sina pesa ya akiba" na kusubiri mshahara mkubwa. Anza na kiasi kidogo.',
                'action' => 'Weka lengo la akiba ya dharura kwenye PesaPlan. Anza leo hata kwa TZS 5,000.',
                'exercise' => 'Hesabu mahitaji muhimu ya mwezi wako. Gawanya kwa 3 — hiyo ni lengo lako la kwanza la wiki 12 zijazo.',
            ],
            [
                'slug' => 'deni-na-malipo',
                'title' => 'Kuelewa deni na majukumu ya malipo',
                'icon' => 'fa-hand-holding-dollar',
                'summary' => 'Deni sio mbaya kila wakati — lakini lisidhibitiwe.',
                'explanation' => 'Deni ni wajibu wa kurudisha pesa. Deni lenye riba (kama mikopo ya benki) linaweza kukuuma kwa muda mrefu. Deni lisilo na riba (kama mkopo wa rafiki) linaweza kuwa salama lakini linaharibu mahusiano kama hulipwi. Deni sharti litambue mapema: kiasi, muda wa malipo, na riba (kama ipo). Akiba ya dharura ni bora kuliko deni. Lakini kama deni ni lazima, litumie kwa jambo linaloongeza thamani (elimu, biashara), sio kwa starehe.',
                'example' => 'Mfano: Deni la TZS 500,000 likiwa na riba ya 20% kwa mwaka, baada ya mwaka 1 unadaiwa TZS 600,000. Kama hulipi haraka, linaongezeka kwa kasi. Lengo: lipa deni lenye riba kubwa kwanza.',
                'mistake' => 'Kosa: kuchukua deni jipya kulipia deni la zamani bila kubadilisha tabia ya matumizi.',
                'action' => 'Orodhesha madeni yote yasiyolipwa. Kwa kila moja, andika: kiasi, muda wa malipo, riba. Anza kulipia lenye riba kubwa.',
                'exercise' => 'Hesabu jumla ya madeni yako. Linganisha na mapato ya mwezi. Je, unaweza kuyalipa ndani ya miezi 6?',
            ],
            [
                'slug' => 'gharama-nafuu',
                'title' => 'Gharama ya fursa na maamuzi ya ununuzi',
                'icon' => 'fa-route',
                'summary' => 'Kila shilingi unayotumia hapa, haipatikani kwa mahali pengine',
                'explanation' => 'Gharama ya fursa (opportunity cost) ni kile ambacho ungepata kama ungetumia pesa kwa njia nyingine. Kila ununuzi una gharama mbili: bei ya bidhaa, na kile unachokosa kwa kutumia pesa hii. Mfano: kama unatumia TZS 50,000 kwa burudani, gharama ya fursa ni TZS 50,000 ambayo singelenga kwenye akiba. Hii haihumaanishi usifanye starehe — lakini fahamu kila uamuzi una gharama. Mtu mwerevu wa fedha hufikiria: "Kwa TZS hii, ningeweza kupata nini bado?"',
                'example' => 'Mfano: Unataka kusajiliwa kozi ya TZS 100,000 au kununua simu mpya ya TZS 100,000. Kama unachagua simu, gharama ya fursa ni kozi ambayo singekupa kipaji kipya. Kama unachagua kozi, baada ya mwaka unaweza kupata kazi ya TZS 200,000 zaidi kwa mwezi.',
                'mistake' => 'Kosa: kuangalia bei tu, bila kufikiri kile unachokosa.',
                'action' => 'Kabla ya ununuzi wa TZS 50,000 au zaidi, uliza: "Kwa hii, ningeweza kununua/nufaika na nini bado?"',
                'exercise' => 'Fikiria ununuzi wa hivi karibuni wa kati ya TZS 30,000+. Andika 2 vingine ambavyo usingeweza kufanya na pesa hiyo.',
            ],
            [
                'slug' => 'ukaguzi-wa-kila-siku',
                'title' => 'Ukaguzi wa kila siku, kila wiki, na kila mwezi',
                'icon' => 'fa-calendar-check',
                'summary' => 'Mazoea madogo ya ukaguzi hujenga nidhamu ya muda mrefu',
                'explanation' => 'Bila ukaguzi wa mara kwa mara, huwezi kujua kama unafuata mpango wako. Ukaguzi wa kila siku (dakika 5): weka miamala ya leo, angalia bajeti iliyobaki. Ukaguzi wa kila wiki (dakika 20): linganisha bajeti na halisi, tambua mifumo. Ukaguzi wa kila mwezi (dakika 30-60): thibitisha mshahara, angalia jumla, tengeneza bajeti ya mwezi ujao. Ukaguzi sio adhabu — ni kujifunza. Ukaguzi unafanya uongoze pesa zako, badala ya pesa zakuongoza.',
                'example' => 'Mfano: Kila siku alasiri, kabla ya kufunga kazi, weka miamala ya leo. Kila Jumapili alasiri, angalia wiki iliyopita. Kila tarehe 28, angalia mwezi mzima.',
                'mistake' => 'Kosa: kufanya ukaguzi wa mwezi pekee, halafu kusahau. Habari ndogo huonekana tu kwa ukaguzi wa kila siku.',
                'action' => 'Weka kumbusho la siku 3: kila alasiri (siku), kila Jumapili (wiki), kila tarehe 28 (mwezi).',
                'exercise' => 'Tumia kipengele cha "Ukaguzi" kwenye PesaPlan. Anza na ukaguzi wa leo wa kila siku.',
            ],
            [
                'slug' => 'matumizi-madogo',
                'title' => 'Kutambua matumizi madogo yanayojirudia',
                'icon' => 'fa-magnifying-glass',
                'summary' => 'Vidonge vidogo vya pesa hupotea bila kujulikana',
                'explanation' => 'Watu wengi hupoteza pesa kupitia "matumizi madogo" yanayojirudia: chai na keki kila siku, hodi za bodaboda zisizohitajika, vitu vya bei nafuu vinavyovunjika haraka. TZS 3,000 kwa siku = TZS 90,000 kwa mwezi = TZS 1,080,000 kwa mwaka. Hii ni pesa kubwa inayopotea bila kujulikana. Ukaguzi wa miamala unafunua mifumo hii. Baada ya kuangalia mwezi mmoja wa miamala, utaona kategoria zinazokula pesa zaidi kuliko ulivyofikiri.',
                'example' => 'Mfano: Unaagiza chai na keki ya TZS 3,500 kila siku kwenye ofisi. Mwezi: TZS 105,000. Mwaka: TZS 1,260,000. Hii ni sawa na mshahara wa mwezi mmoja! Kama unapunguza nusu, unaokoa TZS 630,000 kwa mwaka.',
                'mistake' => 'Kosa: kupuuza matumizi madogo kwa kusema "ni shilingi chache tu".',
                'action' => 'Angalia ripoti ya "matumizi kwa kategoria" ya mwezi mzima. Tambua 3 kategoria zinazokula pesa zaidi ya ulivyofikiri.',
                'exercise' => 'Orodhesha matumizi madogo unayofanya kila siku. Hesabu gharama ya mwezi na mwaka.',
            ],
            [
                'slug' => 'matumizi-yasiyo ya-kawaida',
                'title' => 'Kupanga kwa matumizi yasiyo ya kawaida na ya mwaka',
                'icon' => 'fa-calendar-days',
                'summary' => 'Siku kuu na shughuli za mwaka haziwezi kukushangaza',
                'explanation' => 'Kuna matumizi yanayojirudia kila mwaka lakini hayapatikani kila mwezi: Sikukuu, karo la shule, bima ya gari, hafla ya harusi. Kosa la kawaida: kusubiri hadi siku hiyo, halafu kukopa. Suluhisho: giza gharama ya mwaka kwa 12, akiba kiasi hicho kila mwezi. Mfano: karo la TZS 1,200,000 kwa mwaka = TZS 100,000 kwa mwezi. Hii inakuwa sehemu ya bajeti ya kila mwezi, sio mshtuko wa mwisho wa mwaka.',
                'example' => 'Mfano: Sikukuu ya Desemba inahitaji TZS 300,000. Anza Februari: TZS 30,000 kwa mwezi kwa miezi 10. Desemba ufika tayari una pesa, bila kukopa.',
                'mistake' => 'Kosa: kufikiri "Desemba nitapanga wakati ufike". Utakosa pesa na kukopa.',
                'action' => 'Orodhesha matumizi yote ya mwaka (Sikukuu, bima, karo, hafla). Hesabu na ugawe kwa 12. Weka kwenye bajeti ya kila mwezi.',
                'exercise' => 'Andika matumizi 5 yasiyo ya kawaida yanayokuja mwaka huu. Hesabu jumla na ugawe kwa 12.',
            ],
            [
                'slug' => 'rekebisha-makosa',
                'title' => 'Jinsi ya kurekebisha makosa ya fedha bila kuachwa',
                'icon' => 'fa-arrow-rotate-left',
                'summary' => 'Kila mmoja anafanya makosa — muhimu ni kujifunza na kuendelea',
                'explanation' => 'Watu wengi wanaacha kufuata bajeti baada ya kosa moja: "Nimetumia zaidi ya bajeti leo, basi nimekata tamaa." Lakini makosa ni sehemu ya mchakato. Muhimu ni kuyatambua, kujifunza, na kurekebisha. Kama umetumia zaidi leo, punguza kesho. Kama ulipoteza akiba, anza tena. Kosa moja haliharibu mwezi mzima — lakini kukata tamaa kunaleta hasara kubwa. Nidhamu ya fedha si ukamilifu — ni mazoea ya kuendelea kupitia makosa.',
                'example' => 'Mfano: Bajeti ya starehe ilizidi kwa TZS 50,000. Badala ya kukata tamaa, chukua TZS 50,000 kutoka starehe ya wiki ijayo. Mwezi bado unaweza kuwa mzuri.',
                'mistake' => 'Kosa: kufikiri "Nimeshindwa, basi nitaacha kufuata bajeti".',
                'action' => 'Kama unapita bajeti, rekebisha mara moja. Usisubiri mwezi ujao.',
                'exercise' => 'Fikiria kosa la mwisho la fedha ulilofanya. Andika: 1) lilikuwa nini, 2) ulijifunza nini, 3) utafanya nini tofauti.',
            ],
        ];
    }
}
