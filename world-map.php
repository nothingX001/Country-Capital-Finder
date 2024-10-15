<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>World Map with Capitals</title>
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.css" rel="stylesheet">
    <link href="styles.css" rel="stylesheet">
    <link href="world-map-styles.css" rel="stylesheet">
</head>
<body>

<?php include 'navbar.php'; ?>

<div id="main-world-map">
    <h1>World Map of Capitals</h1>
    <p>Explore the capitals of countries around the world.</p>
    <div id="map" style="height: 500px; border-radius: 15px;"></div>
</div>

<script src="https://api.mapbox.com/mapbox-gl-js/v2.9.1/mapbox-gl.js"></script>
<script>
    mapboxgl.accessToken = 'pk.eyJ1IjoiZGNobzIwMDEiLCJhIjoiY20yYW04bHdtMGl3YjJyb214YXB5dzBtbSJ9.Zs-Gl2JsEgUrU3qTi4gy4w';

    const map = new mapboxgl.Map({
        container: 'map',
        style: 'mapbox://styles/mapbox/standard-satellite', // Updated to use the standard satellite style
        center: [0, 20],
        zoom: 1.5,
        projection: 'globe' // Enables the 3D globe view
    });

    map.on('style.load', () => {
        map.setFog({}); // Adds atmospheric effect for depth perception on the globe
    });

    const countries = [
    { country: "Afghanistan", capitals: ["Kabul", "Kandahar"], coordinates: [[69.1833, 34.5167], [65.7101, 31.6136]], flag: "🇦🇫" },
    { country: "Albania", capitals: ["Tirana"], coordinates: [[19.8189, 41.3275]], flag: "🇦🇱" },
    { country: "Algeria", capitals: ["Algiers"], coordinates: [[3.0588, 36.7372]], flag: "🇩🇿" },
    { country: "Andorra", capitals: ["Andorra la Vella"], coordinates: [[1.5211, 42.5078]], flag: "🇦🇩" },
    { country: "Angola", capitals: ["Luanda"], coordinates: [[13.2300, -8.8383]], flag: "🇦🇴" },
    { country: "Antigua and Barbuda", capitals: ["Saint John's"], coordinates: [[-61.8456, 17.1274]], flag: "🇦🇬" },
    { country: "Argentina", capitals: ["Buenos Aires"], coordinates: [[-58.3833, -34.6118]], flag: "🇦🇷" },
    { country: "Armenia", capitals: ["Yerevan"], coordinates: [[44.5090, 40.1833]], flag: "🇦🇲" },
    { country: "Australia", capitals: ["Canberra"], coordinates: [[149.1281, -35.2835]], flag: "🇦🇺" },
    { country: "Austria", capitals: ["Vienna"], coordinates: [[16.3634, 48.2082]], flag: "🇦🇹" },
    { country: "Azerbaijan", capitals: ["Baku"], coordinates: [[49.8671, 40.4093]], flag: "🇦🇿" },
    { country: "Bahamas", capitals: ["Nassau"], coordinates: [[-77.3504, 25.0343]], flag: "🇧🇸" },
    { country: "Bahrain", capitals: ["Manama"], coordinates: [[50.5860, 26.2235]], flag: "🇧🇭" },
    { country: "Bangladesh", capitals: ["Dhaka"], coordinates: [[90.4125, 23.8103]], flag: "🇧🇩" },
    { country: "Barbados", capitals: ["Bridgetown"], coordinates: [[-59.6167, 13.1000]], flag: "🇧🇧" },
    { country: "Belarus", capitals: ["Minsk"], coordinates: [[27.5667, 53.9000]], flag: "🇧🇾" },
    { country: "Belgium", capitals: ["Brussels"], coordinates: [[4.3497, 50.8503]], flag: "🇧🇪" },
    { country: "Belize", capitals: ["Belmopan"], coordinates: [[-88.7669, 17.2514]], flag: "🇧🇿" },
    { country: "Benin", capitals: ["Porto-Novo", "Cotonou"], coordinates: [[2.6167, 6.4969], [2.4183, 6.3703]], flag: "🇧🇯" },
    { country: "Bhutan", capitals: ["Thimphu"], coordinates: [[89.6390, 27.4728]], flag: "🇧🇹" },
    { country: "Bolivia", capitals: ["Sucre", "La Paz"], coordinates: [[-65.2619, -19.0333], [-68.1193, -16.5000]], flag: "🇧🇴" },
    { country: "Bosnia and Herzegovina", capitals: ["Sarajevo"], coordinates: [[18.4131, 43.8563]], flag: "🇧🇦" },
    { country: "Botswana", capitals: ["Gaborone"], coordinates: [[25.9164, -24.6545]], flag: "🇧🇼" },
    { country: "Brazil", capitals: ["Brasília"], coordinates: [[-47.9292, -15.7801]], flag: "🇧🇷" },
    { country: "Brunei", capitals: ["Bandar Seri Begawan"], coordinates: [[114.9460, 4.9031]], flag: "🇧🇳" },
    { country: "Bulgaria", capitals: ["Sofia"], coordinates: [[23.3219, 42.6977]], flag: "🇧🇬" },
    { country: "Burkina Faso", capitals: ["Ouagadougou"], coordinates: [[-1.5339, 12.3714]], flag: "🇧🇫" },
    { country: "Burundi", capitals: ["Gitega"], coordinates: [[29.9306, -3.4274]], flag: "🇧🇮" },
    { country: "Cabo Verde", capitals: ["Praia"], coordinates: [[-23.5087, 14.9330]], flag: "🇨🇻" },
    { country: "Cambodia", capitals: ["Phnom Penh"], coordinates: [[104.9160, 11.5625]], flag: "🇰🇭" },
    { country: "Cameroon", capitals: ["Yaoundé"], coordinates: [[11.5167, 3.8480]], flag: "🇨🇲" },
    { country: "Canada", capitals: ["Ottawa"], coordinates: [[-75.6972, 45.4215]], flag: "🇨🇦" },
    { country: "Central African Republic", capitals: ["Bangui"], coordinates: [[18.5582, 4.3947]], flag: "🇨🇫" },
    { country: "Chad", capitals: ["N'Djamena"], coordinates: [[15.0544, 12.1348]], flag: "🇹🇩" },
    { country: "Chile", capitals: ["Santiago"], coordinates: [[-70.6483, -33.4569]], flag: "🇨🇱" },
    { country: "China", capitals: ["Beijing"], coordinates: [[116.4074, 39.9042]], flag: "🇨🇳" },
    { country: "Colombia", capitals: ["Bogotá"], coordinates: [[-74.0721, 4.7110]], flag: "🇨🇴" },
    { country: "Comoros", capitals: ["Moroni"], coordinates: [[43.2551, -11.7022]], flag: "🇰🇲" },
    { country: "Republic of the Congo", capitals: ["Brazzaville"], coordinates: [[15.2847, -4.2634]], flag: "🇨🇬" },
    { country: "Democratic Republic of the Congo", capitals: ["Kinshasa"], coordinates: [[15.2663, -4.4419]], flag: "🇨🇩" },
    { country: "Costa Rica", capitals: ["San José"], coordinates: [[-84.0907, 9.9281]], flag: "🇨🇷" },
    { country: "Côte d'Ivoire", capitals: ["Yamoussoukro", "Abidjan"], coordinates: [[-5.2804, 6.8276], [-4.0083, 5.3097]], flag: "🇨🇮" },
    { country: "Croatia", capitals: ["Zagreb"], coordinates: [[15.9819, 45.8144]], flag: "🇭🇷" },
    { country: "Cuba", capitals: ["Havana"], coordinates: [[-82.3666, 23.1136]], flag: "🇨🇺" },
    { country: "Cyprus", capitals: ["Nicosia"], coordinates: [[33.3823, 35.1856]], flag: "🇨🇾" },
    { country: "Czech Republic", capitals: ["Prague"], coordinates: [[14.4378, 50.0755]], flag: "🇨🇿" },
    { country: "Denmark", capitals: ["Copenhagen"], coordinates: [[12.5683, 55.6761]], flag: "🇩🇰" },
    { country: "Djibouti", capitals: ["Djibouti"], coordinates: [[43.1450, 11.5721]], flag: "🇩🇯" },
    { country: "Dominica", capitals: ["Roseau"], coordinates: [[-61.3870, 15.2976]], flag: "🇩🇲" },
    { country: "Dominican Republic", capitals: ["Santo Domingo"], coordinates: [[-69.9312, 18.4861]], flag: "🇩🇴" }, 
    { country: "Ecuador", capitals: ["Quito"], coordinates: [[-78.4678, -0.1807]], flag: "🇪🇨" },
    { country: "Egypt", capitals: ["Cairo"], coordinates: [[31.2357, 30.0444]], flag: "🇪🇬" },
    { country: "El Salvador", capitals: ["San Salvador"], coordinates: [[-89.2182, 13.6929]], flag: "🇸🇻" },
    { country: "Equatorial Guinea", capitals: ["Malabo", "Oyala"], coordinates: [[8.7832, 3.7500], [10.5654, 1.5889]], flag: "🇬🇶" },
    { country: "Eritrea", capitals: ["Asmara"], coordinates: [[38.9251, 15.3229]], flag: "🇪🇷" },
    { country: "Estonia", capitals: ["Tallinn"], coordinates: [[24.7535, 59.4370]], flag: "🇪🇪" },
    { country: "Eswatini", capitals: ["Mbabane", "Lobamba"], coordinates: [[31.1411, -26.3054], [31.2064, -26.4667]], flag: "🇸🇿" },
    { country: "Ethiopia", capitals: ["Addis Ababa"], coordinates: [[38.7578, 9.0300]], flag: "🇪🇹" },
    { country: "Fiji", capitals: ["Suva"], coordinates: [[178.4419, -18.1416]], flag: "🇫🇯" },
    { country: "Finland", capitals: ["Helsinki"], coordinates: [[24.9458, 60.1695]], flag: "🇫🇮" },
    { country: "France", capitals: ["Paris"], coordinates: [[2.3522, 48.8566]], flag: "🇫🇷" },
    { country: "Gabon", capitals: ["Libreville"], coordinates: [[9.4526, 0.4162]], flag: "🇬🇦" },
    { country: "Gambia", capitals: ["Banjul"], coordinates: [[-16.5917, 13.4549]], flag: "🇬🇲" },
    { country: "Georgia", capitals: ["Tbilisi"], coordinates: [[44.8271, 41.7151]], flag: "🇬🇪" },
    { country: "Germany", capitals: ["Berlin"], coordinates: [[13.4050, 52.5200]], flag: "🇩🇪" },
    { country: "Ghana", capitals: ["Accra"], coordinates: [[-0.1869, 5.6037]], flag: "🇬🇭" },
    { country: "Greece", capitals: ["Athens"], coordinates: [[23.7275, 37.9838]], flag: "🇬🇷" },
    { country: "Grenada", capitals: ["Saint George's"], coordinates: [[-61.7486, 12.0564]], flag: "🇬🇩" },
    { country: "Guatemala", capitals: ["Guatemala City"], coordinates: [[-90.5069, 14.6349]], flag: "🇬🇹" },
    { country: "Guinea", capitals: ["Conakry"], coordinates: [[-13.7000, 9.5092]], flag: "🇬🇳" },
    { country: "Guinea-Bissau", capitals: ["Bissau"], coordinates: [[-15.1804, 11.8596]], flag: "🇬🇼" },
    { country: "Guyana", capitals: ["Georgetown"], coordinates: [[-58.1551, 6.8013]], flag: "🇬🇾" },
    { country: "Haiti", capitals: ["Port-au-Prince"], coordinates: [[-72.3345, 18.5392]], flag: "🇭🇹" },
    { country: "Honduras", capitals: ["Tegucigalpa"], coordinates: [[-87.2068, 14.0723]], flag: "🇭🇳" },
    { country: "Hungary", capitals: ["Budapest"], coordinates: [[19.0402, 47.4979]], flag: "🇭🇺" },
    { country: "Iceland", capitals: ["Reykjavik"], coordinates: [[-21.8277, 64.1355]], flag: "🇮🇸" },
    { country: "India", capitals: ["New Delhi"], coordinates: [[77.1025, 28.7041]], flag: "🇮🇳" },
    { country: "Indonesia", capitals: ["Jakarta"], coordinates: [[106.8650, -6.1751]], flag: "🇮🇩" },
    { country: "Iran", capitals: ["Tehran"], coordinates: [[51.3890, 35.6892]], flag: "🇮🇷" },
    { country: "Iraq", capitals: ["Baghdad"], coordinates: [[44.3661, 33.3152]], flag: "🇮🇶" },
    { country: "Ireland", capitals: ["Dublin"], coordinates: [[-6.2603, 53.3498]], flag: "🇮🇪" },
    { country: "Israel", capitals: ["Jerusalem"], coordinates: [[35.2137, 31.7683]], flag: "🇮🇱" },
    { country: "Italy", capitals: ["Rome"], coordinates: [[12.4964, 41.9028]], flag: "🇮🇹" },
    { country: "Jamaica", capitals: ["Kingston"], coordinates: [[-76.7920, 18.0179]], flag: "🇯🇲" },
    { country: "Japan", capitals: ["Tokyo"], coordinates: [[139.6917, 35.6895]], flag: "🇯🇵" },
    { country: "Jordan", capitals: ["Amman"], coordinates: [[35.9121, 31.9454]], flag: "🇯🇴" },
    { country: "Kazakhstan", capitals: ["Nur-Sultan"], coordinates: [[71.4281, 51.1694]], flag: "🇰🇿" },
    { country: "Kenya", capitals: ["Nairobi"], coordinates: [[36.8219, -1.2921]], flag: "🇰🇪" },
    { country: "Kiribati", capitals: ["South Tarawa"], coordinates: [[173.0000, -1.3278]], flag: "🇰🇮" },
    { country: "North Korea", capitals: ["Pyongyang"], coordinates: [[125.7625, 39.0392]], flag: "🇰🇵" },
    { country: "South Korea", capitals: ["Seoul"], coordinates: [[126.9780, 37.5665]], flag: "🇰🇷" },
    { country: "Kosovo", capitals: ["Pristina"], coordinates: [[21.1655, 42.6629]], flag: "🇽🇰" },
    { country: "Kuwait", capitals: ["Kuwait City"], coordinates: [[47.9774, 29.3759]], flag: "🇰🇼" },
    { country: "Kyrgyzstan", capitals: ["Bishkek"], coordinates: [[74.5698, 42.8746]], flag: "🇰🇬" },
    { country: "Laos", capitals: ["Vientiane"], coordinates: [[102.6341, 17.9757]], flag: "🇱🇦" },
    { country: "Latvia", capitals: ["Riga"], coordinates: [[24.1052, 56.9496]], flag: "🇱🇻" },
    { country: "Lebanon", capitals: ["Beirut"], coordinates: [[35.4955, 33.8886]], flag: "🇱🇧" },
    { country: "Lesotho", capitals: ["Maseru"], coordinates: [[27.4884, -29.3158]], flag: "🇱🇸" },
    { country: "Liberia", capitals: ["Monrovia"], coordinates: [[-10.7989, 6.2907]], flag: "🇱🇷" },
    { country: "Libya", capitals: ["Tripoli"], coordinates: [[13.1913, 32.8872]], flag: "🇱🇾" },
    { country: "Liechtenstein", capitals: ["Vaduz"], coordinates: [[9.5215, 47.1410]], flag: "🇱🇮" },
    { country: "Lithuania", capitals: ["Vilnius"], coordinates: [[25.2797, 54.6872]], flag: "🇱🇹" },
    { country: "Luxembourg", capitals: ["Luxembourg"], coordinates: [[6.1319, 49.6116]], flag: "🇱🇺" },
    { country: "Madagascar", capitals: ["Antananarivo"], coordinates: [[47.5162, -18.8792]], flag: "🇲🇬" },
    { country: "Malawi", capitals: ["Lilongwe"], coordinates: [[33.7741, -13.9626]], flag: "🇲🇼" },
    { country: "Malaysia", capitals: ["Kuala Lumpur", "Putrajaya"], coordinates: [[101.6869, 3.1390], [101.6947, 2.9264]], flag: "🇲🇾" },
    { country: "Maldives", capitals: ["Malé"], coordinates: [[73.5089, 4.1755]], flag: "🇲🇻" },
    { country: "Mali", capitals: ["Bamako"], coordinates: [[-7.9837, 12.6392]], flag: "🇲🇱" },
    { country: "Malta", capitals: ["Valletta"], coordinates: [[14.5146, 35.8989]], flag: "🇲🇹" },
    { country: "Marshall Islands", capitals: ["Majuro"], coordinates: [[171.3805, 7.0897]], flag: "🇲🇭" },
    { country: "Mauritania", capitals: ["Nouakchott"], coordinates: [[-15.9824, 18.0735]], flag: "🇲🇷" },
    { country: "Mauritius", capitals: ["Port Louis"], coordinates: [[57.5000, -20.1667]], flag: "🇲🇺" },
    { country: "Mexico", capitals: ["Mexico City"], coordinates: [[-99.1332, 19.4326]], flag: "🇲🇽" },
    { country: "Micronesia", capitals: ["Palikir"], coordinates: [[158.2073, 6.9147]], flag: "🇫🇲" },
    { country: "Moldova", capitals: ["Chișinău"], coordinates: [[28.8575, 47.0105]], flag: "🇲🇩" },
    { country: "Monaco", capitals: ["Monaco"], coordinates: [[7.4246, 43.7374]], flag: "🇲🇨" },
    { country: "Mongolia", capitals: ["Ulaanbaatar"], coordinates: [[106.9057, 47.9181]], flag: "🇲🇳" },
    { country: "Montenegro", capitals: ["Podgorica"], coordinates: [[19.2635, 42.4410]], flag: "🇲🇪" },
    { country: "Morocco", capitals: ["Rabat"], coordinates: [[-6.8498, 34.0209]], flag: "🇲🇦" },
    { country: "Mozambique", capitals: ["Maputo"], coordinates: [[32.5732, -25.9692]], flag: "🇲🇿" },
    { country: "Myanmar", capitals: ["Naypyidaw"], coordinates: [[96.1399, 19.7450]], flag: "🇲🇲" },
    { country: "Namibia", capitals: ["Windhoek"], coordinates: [[17.0873, -22.5592]], flag: "🇳🇦" },
    { country: "Nauru", capitals: ["Yaren"], coordinates: [[166.9188, -0.5477]], flag: "🇳🇷" },
    { country: "Nepal", capitals: ["Kathmandu"], coordinates: [[85.3240, 27.7172]], flag: "🇳🇵" },
    { country: "Netherlands", capitals: ["Amsterdam"], coordinates: [[4.9041, 52.3676]], flag: "🇳🇱" },
    { country: "New Zealand", capitals: ["Wellington"], coordinates: [[174.7762, -41.2865]], flag: "🇳🇿" },
    { country: "Nicaragua", capitals: ["Managua"], coordinates: [[-86.2514, 12.1364]], flag: "🇳🇮" },
    { country: "Niger", capitals: ["Niamey"], coordinates: [[2.1170, 13.5126]], flag: "🇳🇪" },
    { country: "Nigeria", capitals: ["Abuja"], coordinates: [[7.4951, 9.0765]], flag: "🇳🇬" },
    { country: "North Macedonia", capitals: ["Skopje"], coordinates: [[21.4270, 41.9981]], flag: "🇲🇰" },
    { country: "Norway", capitals: ["Oslo"], coordinates: [[10.7522, 59.9139]], flag: "🇳🇴" },
    { country: "Oman", capitals: ["Muscat"], coordinates: [[58.5453, 23.5859]], flag: "🇴🇲" },
    { country: "Pakistan", capitals: ["Islamabad"], coordinates: [[73.0479, 33.6844]], flag: "🇵🇰" },
    { country: "Palau", capitals: ["Ngerulmud"], coordinates: [[134.6244, 7.5004]], flag: "🇵🇼" },
    { country: "Palestine", capitals: ["Ramallah", "Jerusalem"], coordinates: [[35.2033, 31.8996], [35.2137, 31.7683]], flag: "🇵🇸" },
    { country: "Panama", capitals: ["Panama City"], coordinates: [[-79.5167, 8.9833]], flag: "🇵🇦" },
    { country: "Papua New Guinea", capitals: ["Port Moresby"], coordinates: [[147.1797, -9.4780]], flag: "🇵🇬" },
    { country: "Paraguay", capitals: ["Asunción"], coordinates: [[-57.5759, -25.2637]], flag: "🇵🇾" },
    { country: "Peru", capitals: ["Lima"], coordinates: [[-77.0428, -12.0464]], flag: "🇵🇪" },
    { country: "Philippines", capitals: ["Manila"], coordinates: [[120.9842, 14.5995]], flag: "🇵🇭" },
    { country: "Poland", capitals: ["Warsaw"], coordinates: [[21.0122, 52.2297]], flag: "🇵🇱" },
    { country: "Portugal", capitals: ["Lisbon"], coordinates: [[-9.1393, 38.7223]], flag: "🇵🇹" },
    { country: "Qatar", capitals: ["Doha"], coordinates: [[51.5310, 25.2867]], flag: "🇶🇦" },
    { country: "Romania", capitals: ["Bucharest"], coordinates: [[26.1025, 44.4268]], flag: "🇷🇴" },
    { country: "Russia", capitals: ["Moscow"], coordinates: [[37.6173, 55.7558]], flag: "🇷🇺" },
    { country: "Rwanda", capitals: ["Kigali"], coordinates: [[30.0619, -1.9579]], flag: "🇷🇼" },
    { country: "Saint Kitts and Nevis", capitals: ["Basseterre"], coordinates: [[-62.7237, 17.3026]], flag: "🇰🇳" },
    { country: "Saint Lucia", capitals: ["Castries"], coordinates: [[-60.9993, 14.0101]], flag: "🇱🇨" },
    { country: "Saint Vincent and the Grenadines", capitals: ["Kingstown"], coordinates: [[-61.2248, 13.1600]], flag: "🇻🇨" },
    { country: "Samoa", capitals: ["Apia"], coordinates: [[-171.7514, -13.8333]], flag: "🇼🇸" },
    { country: "San Marino", capitals: ["San Marino"], coordinates: [[12.4578, 43.9333]], flag: "🇸🇲" },
    { country: "Sao Tome and Principe", capitals: ["São Tomé"], coordinates: [[6.7333, 0.3365]], flag: "🇸🇹" },
    { country: "Saudi Arabia", capitals: ["Riyadh"], coordinates: [[46.6753, 24.7136]], flag: "🇸🇦" },
    { country: "Senegal", capitals: ["Dakar"], coordinates: [[-17.4734, 14.6928]], flag: "🇸🇳" },
    { country: "Serbia", capitals: ["Belgrade"], coordinates: [[20.4573, 44.7866]], flag: "🇷🇸" },
    { country: "Seychelles", capitals: ["Victoria"], coordinates: [[55.4507, -4.6191]], flag: "🇸🇨" },
    { country: "Sierra Leone", capitals: ["Freetown"], coordinates: [[-13.2344, 8.4844]], flag: "🇸🇱" },
    { country: "Singapore", capitals: ["Singapore"], coordinates: [[103.8198, 1.3521]], flag: "🇸🇬" },
    { country: "Slovakia", capitals: ["Bratislava"], coordinates: [[17.1077, 48.1486]], flag: "🇸🇰" },
    { country: "Slovenia", capitals: ["Ljubljana"], coordinates: [[14.5058, 46.0569]], flag: "🇸🇮" },
    { country: "Solomon Islands", capitals: ["Honiara"], coordinates: [[159.9601, -9.4280]], flag: "🇸🇧" },
    { country: "Somalia", capitals: ["Mogadishu"], coordinates: [[45.3182, 2.0469]], flag: "🇸🇴" },
    { country: "South Africa", capitals: ["Pretoria", "Bloemfontein", "Cape Town"], coordinates: [[28.2293, -25.7461], [26.2119, -29.0852], [18.4241, -33.9249]], flag: "🇿🇦" },
    { country: "South Sudan", capitals: ["Juba"], coordinates: [[31.5789, 4.8517]], flag: "🇸🇸" },
    { country: "Spain", capitals: ["Madrid"], coordinates: [[-3.7038, 40.4168]], flag: "🇪🇸" },
    { country: "Sri Lanka", capitals: ["Sri Jayawardenepura Kotte", "Colombo"], coordinates: [[79.9585, 6.9271], [79.8612, 6.9271]], flag: "🇱🇰" },
    { country: "Sudan", capitals: ["Khartoum"], coordinates: [[32.5599, 15.5007]], flag: "🇸🇩" },
    { country: "Suriname", capitals: ["Paramaribo"], coordinates: [[-55.1679, 5.8520]], flag: "🇸🇷" },
    { country: "Sweden", capitals: ["Stockholm"], coordinates: [[18.0686, 59.3293]], flag: "🇸🇪" },
    { country: "Switzerland", capitals: ["Bern"], coordinates: [[7.4474, 46.9470]], flag: "🇨🇭" },
    { country: "Syria", capitals: ["Damascus"], coordinates: [[36.2921, 33.5138]], flag: "🇸🇾" },
    { country: "Taiwan", capitals: ["Taipei"], coordinates: [[121.5654, 25.0330]], flag: "🇹🇼" },
    { country: "Tajikistan", capitals: ["Dushanbe"], coordinates: [[68.7930, 38.5598]], flag: "🇹🇯" },
    { country: "Tanzania", capitals: ["Dodoma"], coordinates: [[35.7460, -6.1630]], flag: "🇹🇿" },
    { country: "Thailand", capitals: ["Bangkok"], coordinates: [[100.5018, 13.7563]], flag: "🇹🇭" },
    { country: "Togo", capitals: ["Lomé"], coordinates: [[1.2235, 6.1319]], flag: "🇹🇬" },
    { country: "Tonga", capitals: ["Nuku'alofa"], coordinates: [[-175.2045, -21.1394]], flag: "🇹🇴" },
    { country: "Trinidad and Tobago", capitals: ["Port of Spain"], coordinates: [[-61.5160, 10.6596]], flag: "🇹🇹" },
    { country: "Tunisia", capitals: ["Tunis"], coordinates: [[10.1658, 36.8065]], flag: "🇹🇳" },
    { country: "Turkey", capitals: ["Ankara"], coordinates: [[32.8597, 39.9334]], flag: "🇹🇷" },
    { country: "Turkmenistan", capitals: ["Ashgabat"], coordinates: [[58.3783, 37.9601]], flag: "🇹🇲" },
    { country: "Tuvalu", capitals: ["Funafuti"], coordinates: [[179.1940, -8.5167]], flag: "🇹🇻" },
    { country: "Uganda", capitals: ["Kampala"], coordinates: [[32.5825, 0.3476]], flag: "🇺🇬" },
    { country: "Ukraine", capitals: ["Kyiv"], coordinates: [[30.5238, 50.4501]], flag: "🇺🇦" },
    { country: "United Arab Emirates", capitals: ["Abu Dhabi"], coordinates: [[54.3773, 24.4539]], flag: "🇦🇪" },
    { country: "United Kingdom", capitals: ["London"], coordinates: [[-0.1278, 51.5074]], flag: "🇬🇧" },
    { country: "United States", capitals: ["Washington, D.C."], coordinates: [[-77.0369, 38.9072]], flag: "🇺🇸" },
    { country: "Uruguay", capitals: ["Montevideo"], coordinates: [[-56.1645, -34.9011]], flag: "🇺🇾" },
    { country: "Uzbekistan", capitals: ["Tashkent"], coordinates: [[69.2401, 41.2995]], flag: "🇺🇿" },
    { country: "Vanuatu", capitals: ["Port Vila"], coordinates: [[168.3273, -17.7333]], flag: "🇻🇺" },
    { country: "Vatican City", capitals: ["Vatican City"], coordinates: [[12.4534, 41.9029]], flag: "🇻🇦" },
    { country: "Venezuela", capitals: ["Caracas"], coordinates: [[-66.9036, 10.4806]], flag: "🇻🇪" },
    { country: "Vietnam", capitals: ["Hanoi"], coordinates: [[105.8342, 21.0285]], flag: "🇻🇳" },
    { country: "Yemen", capitals: ["Sana'a"], coordinates: [[44.2075, 15.3694]], flag: "🇾🇪" },
    { country: "Zambia", capitals: ["Lusaka"], coordinates: [[28.3228, -15.3875]], flag: "🇿🇲" },
    { country: "Zimbabwe", capitals: ["Harare"], coordinates: [[31.0530, -17.8292]], flag: "🇿🇼" }
];

    countries.forEach(country => {
        country.capitals.forEach((capital, index) => {
            const [lng, lat] = country.coordinates[index];
            new mapboxgl.Marker()
                .setLngLat([lng, lat])
                .setPopup(new mapboxgl.Popup().setText(`${country.flag} ${country.country} - ${capital}`))
                .addTo(map);
        });
    });
</script>

</body>
</html>
