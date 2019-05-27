//Tu znajdują się wszystkie Routes do poszczególnych podstron

import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import {Router, RouterModule, Routes} from '@angular/router';
import {HomeComponent} from './home/home.component';
import {DodajComponent} from './dodaj/dodaj.component';
import {StatystykiComponent} from './statystyki/statystyki.component';
import {UstawieniaComponent} from './ustawienia/ustawienia.component';
import {AddproduktComponent} from './dodaj/addprodukt/addprodukt.component';

const routes: Routes = [ // Tutaj mamy Routes , które umożliwiają nam wywołanie danej metody w klasie
  {path: 'home' , component: HomeComponent}, 
  {path: 'dodaj' , component: DodajComponent,
    children: [
      {path: 'addprodukt' , component: AddproduktComponent},
      {path: '' , component: HomeComponent}
    ]
  },
  {path: 'statystyki' , component: StatysticComponent},
  {path: 'ustawienia' , component: SettingComponent},
  {path: '**' , component: HomeComponent}
];

@NgModule({
  declarations: [],
  imports: [
    RouterModule.forRoot(routes, { useHash: true })
  ],
  exports: [RouterModule]
})
export class AppRoutingModule { }
