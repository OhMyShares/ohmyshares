import { Component } from '@angular/core';
import { AuthService } from '../../core/services/auth.service';

@Component({
    selector: 'app-logout',
    templateUrl: './logout.component.html',
    styleUrls: ['./logout.component.scss'],
    standalone: false
})
export class LogoutComponent {

  constructor(
    private authService: AuthService
  ) {
    this.authService.logout();
  }

}
