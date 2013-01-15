//
//  ViewController.h
//  scraper elezioni
//
//  Created by Carlotta Tatti on 07/01/13.
//  Copyright (c) 2013 Carlotta Tatti. All rights reserved.
//

#import <UIKit/UIKit.h>

@interface ViewController : UIViewController <NSURLConnectionDelegate>

@property (nonatomic, strong) NSMutableArray *arrayComuni;
@property (nonatomic, strong) NSMutableArray *arrayProvince;
@property (nonatomic, strong) NSMutableArray *arrayRegioni;
@property (nonatomic, strong) NSMutableArray *arrayAree;
@property (nonatomic, strong) NSMutableArray *arrayDate;

@property (nonatomic, strong) NSMutableString *url;
@property (weak, nonatomic) IBOutlet UILabel *titolo;
@property (weak, nonatomic) IBOutlet UITextField *dataElezioni;
@property (weak, nonatomic) IBOutlet UITextField *tipoElezioni;

- (IBAction)salvaDati:(id)sender;

@end
