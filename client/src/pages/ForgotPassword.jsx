import React, { useState } from "react";
import axios from "axios";
import "./ForgotPassword.css";

const ForgotPassword = () => {
  const [step, setStep] = useState(1);
  const [phone, setPhone] = useState("");
  const [verificationCode, setVerificationCode] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");
  const [message, setMessage] = useState("");
  const [loading, setLoading] = useState(false);

  // Bước 1: Gửi mã xác thực
  const handleSendCode = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage("");
    try {
      await axios.post("/api/v1/forgot-password", { phone });
      setMessage("Mã xác thực đã được gửi về số điện thoại!");
      setStep(2);
    } catch (err) {
      setMessage(
        err.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại!"
      );
    }
    setLoading(false);
  };

  // Bước 2: Xác thực mã và đặt lại mật khẩu
  const handleResetPassword = async (e) => {
    e.preventDefault();
    setLoading(true);
    setMessage("");
    try {
      await axios.post("/api/v1/reset-password", {
        phone,
        verification_code: verificationCode,
        password,
        password_confirmation: passwordConfirmation,
      });
      setMessage("Đặt lại mật khẩu thành công! Bạn có thể đăng nhập lại.");
      setStep(3);
    } catch (err) {
      setMessage(
        err.response?.data?.message || "Có lỗi xảy ra, vui lòng thử lại!"
      );
    }
    setLoading(false);
  };

  return (
    <div className="login-container">
      <h2>Quên mật khẩu</h2>
      {message && (
        <div className={step === 3 ? "success-message" : "error-message"}>
          {message}
        </div>
      )}

      {step === 1 && (
        <form onSubmit={handleSendCode}>
          <div>
            <label>Số điện thoại</label>
            <input
              type="text"
              value={phone}
              onChange={(e) => setPhone(e.target.value)}
              required
            />
          </div>
          <button type="submit" disabled={loading}>
            {loading ? "Đang gửi..." : "Gửi mã xác thực"}
          </button>
        </form>
      )}

      {step === 2 && (
        <form onSubmit={handleResetPassword}>
          <div>
            <label>Mã xác thực</label>
            <input
              type="text"
              value={verificationCode}
              onChange={(e) => setVerificationCode(e.target.value)}
              required
            />
          </div>
          <div>
            <label>Mật khẩu mới</label>
            <input
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          <div>
            <label>Nhập lại mật khẩu mới</label>
            <input
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              required
            />
          </div>
          <button type="submit" disabled={loading}>
            {loading ? "Đang đặt lại..." : "Đặt lại mật khẩu"}
          </button>
        </form>
      )}

      {step === 3 && (
        <div>
          <p className="success-message">Bạn đã đặt lại mật khẩu thành công!</p>
          <a href="/login">Quay lại trang đăng nhập</a>
        </div>
      )}
    </div>
  );
};

export default ForgotPassword; 