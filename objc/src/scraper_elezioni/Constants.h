//
//  Constants.h
//  scraper elezioni
//
//  Created by Carlotta Tatti on 07/01/13.
//  Copyright (c) 2013 Carlotta Tatti. All rights reserved.
//

#ifndef scraper_elezioni_Constants_h
#define scraper_elezioni_Constants_h

// Url base
#define BASE_URL @"http://elezionistorico.interno.it/index.php?"

// Tipo elezioni
// Valori accettati
#define kAssembleaCostituente @"A"
#define kCamera @"C"
#define kSenato @"S"
#define kEuropee @"E"
#define kRegionali @"R"
#define kProvinciali @"P"
#define kComunali @"G"
#define kReferendum @"F"

// Area elezioni
#define kItalia @"I"
#define kItaliaNoVDA @"H"
#define kCircoscrizioneEstero @"E"
#define kVDATrentino @"G"

#define kURLArea @"tpa=I&tpe=A&lev0=0&levsut0=0&es0=S&ms=N"
#define kURLRegione @"tpa=I&tpe=R&lev0=0&levsut0=0&lev1=1&levsut1=1&ne1=1&es0=S&es1=S&ms=N"
#define kURLRegione2 @"tpa=I&tpe=R&lev0=0&levsut0=0&lev1=9&levsut1=1&ne1=9&es0=S&es1=S&ms=N"

#define kURLProvincia @"tpa=I&tpe=P&lev0=0&levsut0=0&lev1=5&levsut1=1&lev2=84&levsut2=2&ne1=5&ne2=84&es0=S&es1=S&es2=S&ms=S"
#define kURLProvincia2 @"tpa=I&tpe=P&lev0=0&levsut0=0&lev1=11&levsut1=1&lev2=44&levsut2=2&ne1=11&ne2=44&es0=S&es1=S&es2=S&ms=S"
#define kURLCollegio @"tpa=I&tpe=L&lev0=0&levsut0=0&lev1=1&levsut1=1&lev2=88&levsut2=2&lev3=1&levsut3=3&ne1=1&ne2=88&ne3=8803&es0=S&es1=S&es2=S&es3=S&ms=S"
#define kURLCollegio2 @"tpa=I&tpe=L&lev0=0&levsut0=0&lev1=1&levsut1=1&lev2=88&levsut2=2&lev3=2&levsut3=3&ne1=1&ne2=88&ne3=8802&es0=S&es1=S&es2=S&es3=S&ms=S"

#define kURLComune @"tpa=I&tpe=C&lev0=0&levsut0=0&lev1=1&levsut1=1&lev2=88&levsut2=2&lev3=1&levsut3=3&lev4=70&levsut4=4&ne1=1&ne2=88&ne3=8801&ne4=88010070&es0=S&es1=S&es2=S&es3=S&es4=N&ms=S"
#define kURLComune2 @"tpa=I&tpe=C&lev0=0&levsut0=0&lev1=1&levsut1=1&lev2=88&levsut2=2&lev3=4&levsut3=3&lev4=1270&levsut4=4&ne1=1&ne2=88&ne3=8804&ne4=88041270&es0=S&es1=S&es2=S&es3=S&es4=N&ms=S"

#endif
