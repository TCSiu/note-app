import React from 'react';
import '../../scss/app.scss'
import Default from './Common/Default';

function Login() {
  return (
    <Default>
      <div className="row d-flex align-content-center flex-wrap vh-100">
        <div className="col-12 d-flex justify-content-center ">
            <div className="alert alert-danger alert-dismissible fade show w-50" role="alert">
                Error
                <button type="button" className="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
        <div className="col-12 d-flex justify-content-center ">
            <div className="card w-50">
                <div className="card-body">
                    <h5 className="card-title h3 d-inline-flex justify-content-between w-100">
                        Login
                        <div className="dropdown">
                            <button className="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                Language
                            </button>
                          
                            {/* @use('App\Commons\Constants', 'Constants')
                            <ul className="dropdown-menu">
                                @foreach (Constants::AVAILABLE_LOCALE as $language_code => $locale)
                                    <li><a class="dropdown-item" href="{{ route('locale', ['locale' => $language_code]) }}">{{ $locale }}</a></li>
                                @endforeach
                            </ul> */}
                        </div>                  
                    </h5>
                    <form action="{{ route('login') }}" method="POST"  id="form_login" className="row needs-validation" noValidate>
                        {/* @csrf */}
                        <div className="col col-12 mb-3">
                            <label htmlFor="input_login_email" className="form-label">User Email</label>
                            <input type="email" name="email" className="form-control" id="input_login_email" placeholder="name@example.com"
                                required />
                        </div>
                        <div className="col col-12 mb-3">
                            <label htmlFor="input_login_password" className="form-label">Password</label>
                            <input type="password" name="password" className="form-control" id="input_login_password"
                                pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,20}$" required />
                            <div id="label_password_tips" className="form-text">
                                password tips
                            </div>
                        </div>
                        <div className="col col-12">
                            <button type="submit" className="btn btn-primary mb-3 btn-success" id="btn_login_submit"
                                style={{minWidth: "75px"}}>Submit</button>
                            <button type="reset" className="btn btn-primary mb-3 btn-danger" style={{minWidth: "75px"}}>Reset</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    </Default>
  )
}

export default Login