<?php
class CI_Email
{
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $from_email;


    function __construct()
    {
        $this->smtp_host =  getenv('MAIL_HOST');
        $this->smtp_port = getenv('MAIL_PORT');
        $this->smtp_username = getenv('MAIL_USERNAME');
        $this->smtp_password = getenv('MAIL_PASSWORD');
        $this->from_email = getenv('MAIL_FROM');
    }
    public function email_template_to_html($email_template, $template_values)
    {
        $template = $email_template;

        foreach ($template_values as $key => $val) {
            $template = str_replace($key, $val, "$template");
        }

        return $template;
    }
    
    public function send_email($email_headers, $email_content)
    {
        $to = $email_headers['to_email'];

        if(ENVIRONMENT === 'development') {
            $to = config_item('default_email_address');
        }

        $from = (isset($email_headers['from_name']) ? $email_headers['from_name'] : 'Project CRE');
        $cc = (isset($email_headers['cc_email']) ? $email_headers['cc_email'] : '');
        $bcc = (isset($email_headers['bcc_email']) ? $email_headers['bcc_email'] : '');
        $subject = (isset($email_headers['email_subject']) ? $email_headers['email_subject'] : '');

        require_once(APPPATH . '/third_party/PHPMailer/PHPMailerAutoload.php');
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->FromName = $from;
        $mail->Subject = $subject;
        $mail->Body = $email_content;

        $mail->setFrom($this->from_email,$from);

        if (isset($email_headers['content_type']) && $email_headers['content_type'] === 'text') {
            $mail->isMail();
            $mail->isHTML(FALSE);
        } else {
            $mail->isHTML(TRUE);
        }

        $mail->isSMTP();
        $mail->SMTPAuth = TRUE;
        $mail->Host = $this->smtp_host;
        $mail->Port = $this->smtp_port;
        $mail->Username = $this->smtp_username;
        $mail->Password = $this->smtp_password;

        if ($to) {
            foreach (explode(',', $to) as $tmp) {
                $mail->addAddress(trim($tmp));
            }
        }

        if ($cc) {
            foreach (explode(',', $cc) as $tmp) {
                $mail->addCC(trim($tmp));
            }
        }

        if ($bcc) {
            foreach (explode(',', $bcc) as $tmp) {
                $mail->addBCC(trim($tmp));
            }
        }

        if ( ! $mail->send()) {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return TRUE;
        }

    }
}