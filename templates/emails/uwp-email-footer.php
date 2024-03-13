<?php
// don't load directly
if ( !defined('ABSPATH') )
    die('-1');

if ( !isset( $email_vars ) ) {
    global $email_vars;
}
?>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                                <!-- End Content -->
                                            </td>
                                        </tr>
                                    </table>
                                    <!-- End Body -->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
				<?php if ( ! empty( $footer_text ) ) { ?>
				<tr>
                    <td align="center" valign="middle" id="template_footer">
						<!-- Footer -->
						<table border="0" cellpadding="10" cellspacing="0" width="100%">
							<tr>
								<td colspan="2" valign="middle" id="footer_text">
									<?php echo $footer_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</td>
							</tr>
						</table>
						<!-- End Footer -->
					</td>
                </tr>
				<?php } ?>
            </table>
        </div>
    </body>
 </html>