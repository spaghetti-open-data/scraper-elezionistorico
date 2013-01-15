//
//  ViewController.m
//  scraper elezioni
//
//  Created by Carlotta Tatti on 07/01/13.
//  Copyright (c) 2013 Carlotta Tatti. All rights reserved.
//

#import "ViewController.h"
#import "Constants.h"
#import "TFHpple.h"
#import "TFHppleElement.h"
#import "CHCSVParser.h"

@interface ViewController ()

@end

@implementation ViewController

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
    [self performSelectorInBackground:@selector(scaricaDati) withObject:nil];
}


- (void)scaricaDati {
    NSString *startUrl = @"http://elezionistorico.interno.it";
    
    NSString *urlString = [NSString stringWithFormat:@"%@/index.php?tpel=%@", startUrl, @"C"];
    NSURL *requestUrl = [NSURL URLWithString:urlString];
    NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
    
    TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
    
    NSString *dateXpathString = @"//div[@class='lista_date']/ul/li/a";
    NSArray *date = [parser searchWithXPathQuery:dateXpathString];
    
    _arrayDate = [NSMutableArray new];
    
    for (int i=0;i<3;i++) {
        TFHppleElement *data = [date objectAtIndex:i];
        NSLog(@"DATA: %@", [[data firstChild] content]);
        NSString *url = [data objectForKey:@"href"];
        //        NSLog(@"LINK: %@", url);
        _arrayAree = [NSMutableArray new];
        
        NSString *startUrl = @"http://elezionistorico.interno.it";
        NSString *urlString = [NSString stringWithFormat:@"%@%@", startUrl, url];
        NSURL *requestUrl = [NSURL URLWithString:urlString];
        NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
        TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
        
        NSString *areeXpathString = @"//div[@class='sezione'][01]";
        NSArray *aree = [parser searchWithXPathQuery:areeXpathString];
        
        // Elenca l'area
        for (TFHppleElement *area in aree) {
            //            NSLog(@"AREA: %@", [[[[[area firstChild] firstChild] firstChild] firstChild] content]);
            NSString *url = [[[[area firstChild] firstChild] firstChild] objectForKey:@"href"];
            //            NSLog(@"LINK: %@", url);
            _arrayRegioni = [NSMutableArray new];
            NSString *startUrl = @"http://elezionistorico.interno.it";
            NSString *urlString = [NSString stringWithFormat:@"%@%@", startUrl, url];
            NSURL *requestUrl = [NSURL URLWithString:urlString];
            NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
            TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
            
            NSString *regioniXpathString = @"//div[@class='sezione'][01]";
            NSArray *regioni = [parser searchWithXPathQuery:regioniXpathString];
            
            // Elenca la regione
            for (TFHppleElement *elemento2 in regioni) {
                NSArray *regioniArray = [elemento2 children];
                for (TFHppleElement *el2 in regioniArray) {
                    NSArray *array2 = [el2 children];
                    for (TFHppleElement *regione in array2) {
                        NSLog(@"REGIONE: %@", [[[regione firstChild] firstChild] content]);
                        _arrayProvince = [NSMutableArray new];
                        NSString *url = [[regione firstChild] objectForKey:@"href"];
                        //                NSLog(@"LINK: %@", url);
                        
                        NSString *startUrl = @"http://elezionistorico.interno.it";
                        NSString *urlString = [NSString stringWithFormat:@"%@%@", startUrl, url];
                        NSURL *requestUrl = [NSURL URLWithString:urlString];
                        NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
                        TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
                        
                        NSString *provinceXpathString = @"//div[@class='sezione'][01]";
                        NSArray *province = [parser searchWithXPathQuery:provinceXpathString];
                        // Elenca l'area
                        for (TFHppleElement *elemento in province) {
                            NSArray *provinceArray = [elemento children];
                            for (TFHppleElement *el in provinceArray) {
                                NSArray *array = [el children];
                                for (TFHppleElement *provincia in array) {
                                    _arrayComuni = [NSMutableArray new];
                                    NSString *url = [[provincia firstChild] objectForKey:@"href"];
                                    NSLog(@"PROVINCIA: %@", [[[provincia firstChild] firstChild] content]);
                                    NSString *startUrl = @"http://elezionistorico.interno.it";
                                    NSString *urlString = [NSString stringWithFormat:@"%@%@", startUrl, url];
                                    NSURL *requestUrl = [NSURL URLWithString:urlString];
                                    NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
                                    TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
                                    
                                    NSString *comuniXpathString = @"//div[@class='sezione'][01]";
                                    NSArray *comuni = [parser searchWithXPathQuery:comuniXpathString];
                                    // Elenca il comune
                                    for (TFHppleElement *elemento in comuni) {
                                        NSArray *array = [elemento children];
                                        for (TFHppleElement *el in array) {
                                            NSArray *array2 = [el children];
                                            for (int i=0;i<array2.count;i++) {
                                                TFHppleElement *comune = [array2 objectAtIndex:i];
                                                NSString *nomeComune = [[[comune firstChild] firstChild] content];
                                                NSLog(@"COMUNE: %@", nomeComune);
                                                NSString *url = [[comune firstChild] objectForKey:@"href"];
                                                //                        NSLog(@"LINK: %@", url);
                                                
                                                NSMutableDictionary *riepilogoDict = [NSMutableDictionary new];
                                                NSMutableDictionary *completoDict = [NSMutableDictionary new];
                                                
                                                NSMutableDictionary *comuneDict = [NSMutableDictionary new];
                                                [comuneDict setValue:nomeComune forKey:@"nome_comune"];
                                                
                                                NSString *startUrl = @"http://elezionistorico.interno.it";
                                                NSString *urlString = [NSString stringWithFormat:@"%@%@", startUrl, url];
                                                NSURL *requestUrl = [NSURL URLWithString:urlString];
                                                NSData *responseData = [NSData dataWithContentsOfURL:requestUrl];
                                                TFHpple *parser = [TFHpple hppleWithHTMLData:responseData];
                                                
                                                NSString *risultatiXpathString = @"//table[@class='dati']";
                                                NSArray *risultati = [parser searchWithXPathQuery:risultatiXpathString];
                                                
                                                NSMutableArray *candidatiArray = [NSMutableArray new];
                                                NSMutableDictionary *candidatoDict;
                                                NSMutableArray *listeArray;
                                                NSMutableDictionary *listaDict;
                                                
                                                for (TFHppleElement *risultato in risultati) {
                                                    NSArray *children = [risultato children];
                                                    for (int i=1;i<children.count;i++) {
                                                        TFHppleElement *el = [children objectAtIndex:i];
                                                        NSString *class = [[el attributes] objectForKey:@"class"];
                                                        if ([class isEqualToString:@"leader"]) {
                                                            listeArray = [NSMutableArray new];
                                                            candidatoDict = [NSMutableDictionary new];
                                                            TFHppleElement *leader = [el firstChild];
                                                            [candidatoDict setValue:[[leader firstChild] content] forKey:@"nome_candidato"];
                                                            [candidatiArray addObject:candidatoDict];
                                                            //                                                            NSLog(@"candidatiArray %@", candidatiArray);
                                                        } else if ([class isEqualToString:@"totale"]) {
                                                            NSArray *dati = [el children];
                                                            TFHppleElement *voti = [dati objectAtIndex:6];
                                                            TFHppleElement *percentuale = [dati objectAtIndex:7];
                                                            [candidatoDict setValue:[[voti firstChild] content] forKey:@"voti"];
                                                            [candidatoDict setValue:[[percentuale firstChild] content] forKey:@"percentuale"];
                                                            //                                                            NSLog(@"totale coalizione %@", candidatoDict);
                                                        } else if ([class isEqualToString:@"totalecomplessivovoti"]) {
                                                            NSArray *dati = [el children];
                                                            TFHppleElement *voti = [dati objectAtIndex:6];
                                                            [completoDict setValue:[[voti firstChild] content] forKey:@"voti"];
                                                            [completoDict setValue:candidatiArray forKey:@"candidati"];
                                                            //                                                            NSLog(@"completoDict %@", completoDict);
                                                        } else {
                                                            NSArray *dati = [el children];
                                                            TFHppleElement *voti = [dati objectAtIndex:7];
                                                            TFHppleElement *percentuale = [dati objectAtIndex:8];
                                                            listaDict = [NSMutableDictionary new];
                                                            [listaDict setValue:[[[dati objectAtIndex:3] firstChild] content] forKey:@"nome_lista"];
                                                            [listaDict setValue:[[voti firstChild] content] forKey:@"voti"];
                                                            [listaDict setValue:[[percentuale firstChild] content] forKey:@"percentuale"];
                                                            //                                                            NSLog(@"totale lista %@", completoDict);
                                                            [listeArray addObject:listaDict];
                                                            [candidatoDict setValue:listeArray forKey:@"liste"];
                                                        }
                                                    }
                                                }
                                                
                                                NSString *elettoriXpathString = @"//table[@class='dati_riepilogo']//tr[1]/td[1]";
                                                NSArray *elettori = [parser searchWithXPathQuery:elettoriXpathString];
                                                // Elenca l'area
                                                for (TFHppleElement *risultato in elettori) {
                                                    NSArray *elements = [risultato children];
                                                    for (TFHppleElement *el in elements) {
                                                        //                                NSLog(@"Elettori: %@", [el content]);
                                                        [riepilogoDict setValue:[el content] forKey:@"elettori"];
                                                    }
                                                }
                                                
                                                NSString *schedeBiancheXpathString = @"//table[@class='dati_riepilogo']//tr/td[1]";
                                                NSArray *schedeBianche = [parser searchWithXPathQuery:schedeBiancheXpathString];
                                                // Elenca l'area
                                                for (TFHppleElement *risultato in schedeBianche) {
                                                    NSArray *elements = [risultato children];
                                                    for (TFHppleElement *el in elements) {
                                                        //                                NSLog(@"Schede bianche: %@", [el content]);
                                                        [riepilogoDict setValue:[el content] forKey:@"schede_bianche"];
                                                    }
                                                }
                                                
                                                NSString *votantiXpathString = @"//table[@class='dati_riepilogo']//tr[1]/td[3]";
                                                NSArray *votanti = [parser searchWithXPathQuery:votantiXpathString];
                                                // Elenca l'area
                                                for (TFHppleElement *risultato in votanti) {
                                                    NSArray *elements = [risultato children];
                                                    for (TFHppleElement *el in elements) {
                                                        //                                NSLog(@"Votanti: %@", [el content]);
                                                        [riepilogoDict setValue:[el content] forKey:@"votanti"];
                                                    }
                                                }
                                                
                                                NSString *nonValideXpathString = @"//table[@class='dati_riepilogo']//tr[2]/td[3]";
                                                NSArray *nonValide = [parser searchWithXPathQuery:nonValideXpathString];
                                                // Elenca l'area
                                                for (TFHppleElement *risultato in nonValide) {
                                                    NSArray *elements = [risultato children];
                                                    for (TFHppleElement *el in elements) {
                                                        //                                NSLog(@"Schede non valide (bianche incl.): %@", [el content]);
                                                        [riepilogoDict setValue:[el content] forKey:@"non_valide"];
                                                    }
                                                }
                                                [comuneDict setValue:riepilogoDict forKey:@"riepilogo"];
                                                [comuneDict setValue:completoDict forKey:@"completo"];
                                                [_arrayComuni addObject:comuneDict];
                                                //                                                NSLog(@"%@", completoDict);
                                            }
                                        }
                                    }
                                    [_arrayProvince addObject:_arrayComuni];
                                    //                                    NSLog(@"%@", _arrayProvince);
                                }
                            }
                        }
                        [_arrayRegioni addObject:_arrayProvince];
                        //                        NSLog(@"%@", _arrayRegioni);
                    }
                }
            }
            [_arrayAree addObject:_arrayRegioni];
            //            NSLog(@"%@", _arrayAree);
        }
    }
    //    TFHppleElement *data = [date objectAtIndex:0];
    //    if (data) {
    //
    //    }
    
    [_arrayDate addObject:_arrayAree];
    //            NSLog(@"%@", _arrayDate);
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (IBAction)salvaDati:(id)sender {
    NSArray *paths = NSSearchPathForDirectoriesInDomains(NSDocumentDirectory,
                                                         NSUserDomainMask, YES);
    NSString *documentsDirectoryPath = [paths objectAtIndex:0];
    NSString *filePath = [documentsDirectoryPath
                          stringByAppendingPathComponent:@"camera.xml"];
    [_arrayDate writeToFile:filePath atomically: YES];
}


@end
